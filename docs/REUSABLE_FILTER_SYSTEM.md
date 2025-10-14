# Reusable Filter System

This document explains how to use the reusable filter/pagination/search system for list endpoints.

## Overview

The system provides a consistent way to handle:
- **Pagination** with `has_more` for infinite scroll
- **Full-text search** across multiple fields
- **Advanced filtering** with operators (=, in, gt, gte, lt, lte, like)
- **Sorting** with customizable fields

## Components

### 1. ListQueryRequest DTO
`app/Application/DTOs/Common/ListQueryRequest.php`

Core DTO that encapsulates all list query parameters:
```php
$listQuery = new ListQueryRequest(
    page: 1,
    perPage: 20,
    search: 'keyword',
    sortBy: 'created_at',
    sortOrder: 'desc',
    filters: [
        'status' => 'active',
        'campaign_status:in' => 'OPEN,IN_REVIEW',
        'budget:gte' => '1000'
    ]
);
```

### 2. HasFilterableFields Trait
`app/Infrastructure/Traits/HasFilterableFields.php`

Add this trait to your Eloquent models to define searchable, filterable, and sortable fields:

```php
class Campaign extends Model
{
    use HasFilterableFields;

    protected static array $searchableFields = [
        'name',
        'description',
    ];

    protected static array $filterableFields = [
        'status' => [
            'type' => 'string',
            'allowed_values' => ['ACTIVE', 'DELETED', 'DISABLED'],
        ],
        'budget' => [
            'type' => 'float',
        ],
    ];

    protected static array $sortableFields = [
        'created_at',
        'name',
        'budget',
    ];
}
```

### 3. QueryFilterService
`app/Infrastructure/Services/QueryFilterService.php`

Reusable service that applies filters, search, sorting, and pagination to any Eloquent query:

```php
$result = $queryFilterService->applyFilters($query, $listQuery, CampaignModel::class);
```

### 4. PaginationResponse DTO
`app/Application/DTOs/Common/PaginationResponse.php`

Standardized pagination metadata with `hasMore` for infinite scroll.

## Usage Examples

### Example 1: Basic List Endpoint

**1. Define searchable/filterable fields in your model:**

```php
class Product extends Model
{
    use HasFilterableFields;

    protected static array $searchableFields = ['name', 'description', 'sku'];

    protected static array $filterableFields = [
        'category_id' => ['type' => 'string'],
        'status' => [
            'type' => 'string',
            'allowed_values' => ['ACTIVE', 'INACTIVE'],
        ],
        'price' => ['type' => 'float'],
    ];

    protected static array $sortableFields = ['created_at', 'name', 'price', 'stock'];
}
```

**2. Create a list request DTO (optional, for validation):**

```php
class ListProductsRequest
{
    public function __construct(
        public readonly ListQueryRequest $listQuery
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            ListQueryRequest::fromArray($data, ['sortBy' => 'created_at'])
        );
    }

    public function validate(): array
    {
        return $this->listQuery->validate(
            allowedSortFields: Product::getSortableFields(),
            allowedFilterFields: Product::getFilterableFields()
        );
    }
}
```

**3. Use QueryFilterService in your repository:**

```php
class ProductRepositoryImpl implements IProductRepository
{
    public function __construct(
        private readonly QueryFilterService $queryFilterService
    ) {}

    public function findAll(ListQueryRequest $listQuery): array
    {
        $query = Product::query()->with(['category']);

        return $this->queryFilterService->applyFilters(
            $query,
            $listQuery,
            Product::class
        );
    }
}
```

### Example 2: Advanced Filtering

**API Request:**
```http
GET /api/v1/products?page=2&per_page=20&search=laptop&filters[status:in]=ACTIVE,FEATURED&filters[price:gte]=500&sort_by=price&sort_order=asc
```

**Parsed as:**
```php
ListQueryRequest(
    page: 2,
    perPage: 20,
    search: 'laptop',
    sortBy: 'price',
    sortOrder: 'asc',
    filters: [
        'status:in' => 'ACTIVE,FEATURED',
        'price:gte' => '500'
    ]
)
```

**Generated SQL:**
```sql
SELECT * FROM products
WHERE (name LIKE '%laptop%' OR description LIKE '%laptop%' OR sku LIKE '%laptop%')
  AND status IN ('ACTIVE', 'FEATURED')
  AND price >= 500
ORDER BY price ASC
LIMIT 20 OFFSET 20
```

### Example 3: Response Format

**Response:**
```json
{
  "success": true,
  "data": {
    "products": [
      {
        "id": "01HPQRS...",
        "name": "Gaming Laptop",
        "price": 1299.99,
        ...
      }
    ],
    "pagination": {
      "currentPage": 2,
      "perPage": 20,
      "total": 156,
      "totalPages": 8,
      "hasMore": true,
      "hasPrevious": true
    }
  }
}
```

## Supported Filter Operators

| Operator | Description | Example |
|----------|-------------|---------|
| `=` (default) | Exact match | `status=ACTIVE` |
| `in` | Match any value in comma-separated list | `status:in=ACTIVE,DELETED` |
| `gt` | Greater than | `price:gt=100` |
| `gte` | Greater than or equal | `price:gte=100` |
| `lt` | Less than | `price:lt=1000` |
| `lte` | Less than or equal | `price:lte=1000` |
| `like` | Pattern match | `name:like=laptop` |

## Filter Field Types

| Type | Description | Example |
|------|-------------|---------|
| `string` | Text field | `'status' => ['type' => 'string']` |
| `int` | Integer | `'quantity' => ['type' => 'int']` |
| `float` | Decimal | `'price' => ['type' => 'float']` |
| `bool` | Boolean | `'is_featured' => ['type' => 'bool']` |
| `date` | ISO 8601 date | `'created_at' => ['type' => 'date']` |

## Infinite Scroll Support

The `hasMore` field in pagination response indicates if there are more items available:

```javascript
// Frontend infinite scroll example
const loadMore = async () => {
  const response = await fetch(`/api/campaigns?page=${nextPage}`);
  const data = await response.json();

  items.push(...data.campaigns);

  if (data.pagination.hasMore) {
    nextPage++;
  } else {
    // No more items
  }
};
```

## Best Practices

1. **Always define allowed fields** in models using the trait
2. **Validate filters** before applying to prevent SQL injection
3. **Limit perPage** to prevent performance issues (max 100)
4. **Use eager loading** to prevent N+1 queries
5. **Index filterable/sortable fields** in database
6. **Cache searchable fields** if they don't change often

## Migration Guide

**Old format:**
```php
$campaigns = $repository->findByAdvertiserCompany(
    advertiserCompanyId: $id,
    filters: ['status' => 'ACTIVE'],
    sortBy: 'created_at',
    sortOrder: 'desc',
    page: 1,
    perPage: 10
);
```

**New format:**
```php
$listQuery = new ListQueryRequest(
    page: 1,
    perPage: 10,
    sortBy: 'created_at',
    sortOrder: 'desc',
    filters: ['status' => 'ACTIVE']
);

$campaigns = $repository->findByAdvertiserCompanyWithFilters(
    advertiserCompanyId: $id,
    listQuery: $listQuery
);
```

## Testing

```php
public function test_list_campaigns_with_filters()
{
    Campaign::factory()->create(['status' => 'ACTIVE', 'name' => 'Test']);
    Campaign::factory()->create(['status' => 'DELETED']);

    $listQuery = new ListQueryRequest(
        search: 'Test',
        filters: ['status' => 'ACTIVE']
    );

    $result = $this->repository->findAll($listQuery);

    $this->assertCount(1, $result['data']);
    $this->assertTrue($result['hasMore'] === false);
}
```
