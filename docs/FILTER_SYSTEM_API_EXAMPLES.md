# Filter System API Examples

## Campaign List Endpoint

### Basic Request
```http
GET /api/v1/advertiser/campaigns?page=1&per_page=20
Authorization: Bearer {token}
```

### With Search
```http
GET /api/v1/advertiser/campaigns?search=summer&page=1&per_page=20
```

### With Simple Filters
```http
GET /api/v1/advertiser/campaigns?status=ACTIVE&campaignStatus=OPEN&page=1
```

### With Advanced Filters (Operators)
```http
GET /api/v1/advertiser/campaigns?filters[status:in]=ACTIVE,DELETED&filters[campaignStatus]=IN_REVIEW&page=1
```

### With Sorting
```http
GET /api/v1/advertiser/campaigns?sortBy=createdAt&sortOrder=desc&page=1
```

### Complete Example
```http
GET /api/v1/advertiser/campaigns?search=campaign&filters[campaignStatus:in]=OPEN,IN_REVIEW&sortBy=budget&sortOrder=desc&page=2&perPage=15
Authorization: Bearer {token}
```

## Response Format

```json
{
  "success": true,
  "data": {
    "campaigns": [
      {
        "id": "01HQRS...",
        "name": "Summer Campaign",
        "description": "Summer sale campaign",
        "goal": "AWARENESS",
        "budget": 5000,
        "desiredViews": 100000,
        "campaignStatus": "OPEN",
        "status": "ACTIVE",
        ...
      }
    ],
    "pagination": {
      "currentPage": 2,
      "perPage": 15,
      "total": 48,
      "totalPages": 4,
      "hasMore": true,
      "hasPrevious": true,
      "hasNextPage": true,
      "hasPreviousPage": true
    }
  }
}
```

## Infinite Scroll Implementation

### JavaScript/React Example
```javascript
import { useState, useEffect } from 'react';

function CampaignList() {
  const [campaigns, setCampaigns] = useState([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [loading, setLoading] = useState(false);

  const loadMore = async () => {
    if (loading || !hasMore) return;

    setLoading(true);
    const response = await fetch(
      `/api/v1/advertiser/campaigns?page=${page}&perPage=20`,
      {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      }
    );

    const data = await response.json();

    setCampaigns(prev => [...prev, ...data.data.campaigns]);
    setHasMore(data.data.pagination.hasMore);
    setPage(prev => prev + 1);
    setLoading(false);
  };

  useEffect(() => {
    loadMore();
  }, []);

  return (
    <div>
      {campaigns.map(campaign => (
        <CampaignCard key={campaign.id} campaign={campaign} />
      ))}

      {hasMore && (
        <button onClick={loadMore} disabled={loading}>
          {loading ? 'Loading...' : 'Load More'}
        </button>
      )}
    </div>
  );
}
```

### Vue.js Example
```vue
<template>
  <div>
    <div v-for="campaign in campaigns" :key="campaign.id">
      <CampaignCard :campaign="campaign" />
    </div>

    <button
      v-if="hasMore"
      @click="loadMore"
      :disabled="loading"
    >
      {{ loading ? 'Loading...' : 'Load More' }}
    </button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const campaigns = ref([]);
const page = ref(1);
const hasMore = ref(true);
const loading = ref(false);

const loadMore = async () => {
  if (loading.value || !hasMore.value) return;

  loading.value = true;

  const response = await fetch(
    `/api/v1/advertiser/campaigns?page=${page.value}&perPage=20`,
    {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }
  );

  const data = await response.json();

  campaigns.value.push(...data.data.campaigns);
  hasMore.value = data.data.pagination.hasMore;
  page.value++;
  loading.value = false;
};

onMounted(() => {
  loadMore();
});
</script>
```

## Filter UI Examples

### Search + Filters Component
```javascript
function CampaignFilters({ onFilterChange }) {
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState([]);
  const [campaignStatus, setCampaignStatus] = useState([]);

  const buildQueryString = () => {
    const params = new URLSearchParams();

    if (search) params.append('search', search);

    if (status.length > 0) {
      params.append('filters[status:in]', status.join(','));
    }

    if (campaignStatus.length > 0) {
      params.append('filters[campaignStatus:in]', campaignStatus.join(','));
    }

    return params.toString();
  };

  const handleApplyFilters = () => {
    onFilterChange(buildQueryString());
  };

  return (
    <div className="filters">
      <input
        type="text"
        placeholder="Search campaigns..."
        value={search}
        onChange={(e) => setSearch(e.target.value)}
      />

      <MultiSelect
        label="Status"
        options={['ACTIVE', 'DELETED', 'DISABLED']}
        value={status}
        onChange={setStatus}
      />

      <MultiSelect
        label="Campaign Status"
        options={['OPEN', 'CLOSED', 'IN_REVIEW', 'REJECTED']}
        value={campaignStatus}
        onChange={setCampaignStatus}
      />

      <button onClick={handleApplyFilters}>Apply Filters</button>
    </div>
  );
}
```

## Available Filters

### Campaign Filters

| Field | Type | Operators | Values |
|-------|------|-----------|--------|
| status | string | =, in | ACTIVE, DELETED, DISABLED |
| campaignStatus | string | =, in | OPEN, CLOSED, IN_REVIEW, REJECTED |
| goal | string | =, in | AWARENESS, ENGAGEMENT, TRAFFIC, LEADS, FOLLOWERS_GROWTH |
| search | string | n/a | Searches name, description, targetLink |

### Sortable Fields

- createdAt
- updatedAt
- name
- budget
- desiredViews
- desiredStartDate
- desiredExpireDate
- realStartDate

## Error Responses

### Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "page": "Page number must be at least 1",
    "filters.status": "Filter 'status' must be one of: ACTIVE, DELETED, DISABLED"
  }
}
```

### Unauthorized
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

## Performance Tips

1. **Use pagination**: Keep `perPage` between 10-50 for optimal performance
2. **Filter first, then search**: Filters are more efficient than full-text search
3. **Index database columns**: Ensure all filterable/sortable fields are indexed
4. **Cache results**: Consider caching frequently accessed pages
5. **Debounce search**: Wait for user to finish typing before searching

## Testing with cURL

```bash
# Basic list
curl -X GET "http://localhost/api/v1/advertiser/campaigns?page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# With search
curl -X GET "http://localhost/api/v1/advertiser/campaigns?search=summer&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# With filters
curl -X GET "http://localhost/api/v1/advertiser/campaigns?filters[status:in]=ACTIVE,DELETED&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Complete example
curl -X GET "http://localhost/api/v1/advertiser/campaigns?search=campaign&filters[campaignStatus]=OPEN&sortBy=budget&sortOrder=desc&page=1&perPage=20" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
