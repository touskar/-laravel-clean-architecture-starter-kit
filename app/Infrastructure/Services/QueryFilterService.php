<?php

namespace App\Infrastructure\Services;

use App\Application\DTOs\Common\ListQueryRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * QueryFilterService - Reusable service for applying filters, search, and pagination
 * Can be used by any repository for list queries
 */
class QueryFilterService
{
    /**
     * Apply all filters, search, sorting, and pagination to a query
     *
     * @param  Builder  $query  The Eloquent query builder
     * @param  ListQueryRequest  $listQuery  The list query request DTO
     * @param  string  $entityClass  The domain entity class (for getting searchable/filterable fields)
     * @param  array  $fieldMapping  Mapping from domain fields to database columns (e.g., ['createdAt' => 'created_at'])
     * @return array Paginated result with metadata
     */
    public function applyFilters(Builder $query, ListQueryRequest $listQuery, string $entityClass, array $fieldMapping = []): array
    {
        // Apply search if provided
        if ($listQuery->search !== null && method_exists($entityClass, 'getSearchableFields')) {
            $searchFields = $this->mapFields($entityClass::getSearchableFields(), $fieldMapping);
            $this->applySearch($query, $listQuery->search, $searchFields);
        }

        // Apply filters with operators
        if (! empty($listQuery->filters) && method_exists($entityClass, 'getFilterableFields')) {
            $this->applyParsedFilters($query, $listQuery->getParsedFilters(), $entityClass::getFilterableFields(), $fieldMapping);
        }

        // Apply sorting
        $this->applySorting($query, $listQuery, $entityClass, $fieldMapping);

        // Get total count before pagination
        $total = $query->count();

        // Apply pagination
        $perPage = min(max($listQuery->perPage, 1), 100);
        $page = max($listQuery->page, 1);
        $offset = ($page - 1) * $perPage;

        $results = $query->skip($offset)->take($perPage)->get();

        // Calculate pagination metadata
        $totalPages = (int) ceil($total / $perPage);
        $hasMore = $page < $totalPages;

        return [
            'data' => $results,
            'total' => $total,
            'currentPage' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasMore' => $hasMore,
            'hasNextPage' => $hasMore, // Alias for backwards compatibility
            'hasPrevious' => $page > 1,
            'hasPreviousPage' => $page > 1, // Alias for backwards compatibility
        ];
    }

    /**
     * Apply search across multiple fields (OR condition)
     *
     * @param  Builder  $query  The query builder
     * @param  string  $search  The search term
     * @param  array  $searchableFields  Fields to search in
     */
    private function applySearch(Builder $query, string $search, array $searchableFields): void
    {
        if (empty($searchableFields)) {
            return;
        }

        $query->where(function ($q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'LIKE', "%{$search}%");
            }
        });
    }

    /**
     * Apply parsed filters with operators
     *
     * @param  Builder  $query  The query builder
     * @param  array  $parsedFilters  Parsed filters from FilterRequest
     * @param  array  $filterableFields  Allowed filterable fields from domain entity
     * @param  array  $fieldMapping  Mapping from domain fields to database columns
     */
    private function applyParsedFilters(Builder $query, array $parsedFilters, array $filterableFields, array $fieldMapping): void
    {
        foreach ($parsedFilters as $filter) {
            $field = $filter['field'];
            $operator = $filter['operator'];
            $value = $filter['value'];

            // Check if field is allowed
            if (! isset($filterableFields[$field])) {
                continue;
            }

            // Map domain field to database column
            $dbField = $fieldMapping[$field] ?? $field;

            // Apply filter based on operator
            match ($operator) {
                '=' => $query->where($dbField, '=', $value),
                'in' => $query->whereIn($dbField, (array) $value),
                'gt' => $query->where($dbField, '>', $value),
                'gte' => $query->where($dbField, '>=', $value),
                'lt' => $query->where($dbField, '<', $value),
                'lte' => $query->where($dbField, '<=', $value),
                'like' => $query->where($dbField, 'LIKE', "%{$value}%"),
                default => null,
            };
        }
    }

    /**
     * Apply sorting to query
     *
     * @param  Builder  $query  The query builder
     * @param  ListQueryRequest  $listQuery  The list query request
     * @param  string  $entityClass  The domain entity class
     * @param  array  $fieldMapping  Mapping from domain fields to database columns
     */
    private function applySorting(Builder $query, ListQueryRequest $listQuery, string $entityClass, array $fieldMapping): void
    {
        $sortBy = $listQuery->sortBy;
        $sortOrder = $listQuery->getSortOrder();

        // Get allowed sortable fields
        $sortableFields = method_exists($entityClass, 'getSortableFields')
            ? $entityClass::getSortableFields()
            : [];

        // If no sortBy specified or invalid, use first sortable field or 'createdAt'
        if ($sortBy === null || (! empty($sortableFields) && ! in_array($sortBy, $sortableFields))) {
            $sortBy = ! empty($sortableFields) ? $sortableFields[0] : 'createdAt';
        }

        // Map domain field to database column
        $dbField = $fieldMapping[$sortBy] ?? $sortBy;

        $query->orderBy($dbField, $sortOrder);
    }

    /**
     * Map domain fields to database columns
     *
     * @param  array  $fields  Domain field names
     * @param  array  $mapping  Field mapping
     * @return array Mapped field names
     */
    private function mapFields(array $fields, array $fieldMapping): array
    {
        return array_map(fn ($field) => $fieldMapping[$field] ?? $field, $fields);
    }
}
