<?php

namespace App\Application\DTOs\Common;

/**
 * ListQueryRequest DTO - Reusable list query with pagination, search, and filtering
 * Supports infinite scroll pagination with has_more field
 * Supports advanced filtering: field:operator:value(s)
 *
 * Filter format examples:
 * - Simple equality: ['status' => 'active']
 * - IN operator: ['status:in' => 'active,disabled,deleted']
 * - Multiple filters: ['status:in' => 'active,disabled', 'type' => 'shop']
 *
 * Search: Searches across all searchable fields defined in model
 */
class ListQueryRequest
{
    public function __construct(
        // Pagination
        public readonly int $page = 1,
        public readonly int $perPage = 10,

        // Search (searches across multiple searchable fields)
        public readonly ?string $search = null,

        // Sorting
        public readonly ?string $sortBy = null,
        public readonly string $sortOrder = 'desc',

        // Dynamic filters with operators (field:operator => value)
        // Supported operators: = (default), in, gt, gte, lt, lte, like
        public readonly array $filters = []
    ) {}

    public static function fromArray(array $data, array $defaults = []): self
    {
        return new self(
            page: (int) ($data['page'] ?? $defaults['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? $data['perPage'] ?? $defaults['perPage'] ?? 10),
            search: $data['search'] ?? $defaults['search'] ?? null,
            sortBy: $data['sort_by'] ?? $data['sortBy'] ?? $defaults['sortBy'] ?? null,
            sortOrder: $data['sort_order'] ?? $data['sortOrder'] ?? $defaults['sortOrder'] ?? 'desc',
            filters: $data['filters'] ?? $defaults['filters'] ?? []
        );
    }

    /**
     * Validate the filter request
     *
     * @param  array  $allowedSortFields  Allowed fields for sorting
     * @param  array  $allowedFilterFields  Allowed fields with their validation rules
     * @return array Validation errors
     */
    public function validate(array $allowedSortFields = [], array $allowedFilterFields = []): array
    {
        $errors = [];

        // Validate pagination
        if ($this->page < 1) {
            $errors['page'] = 'Page number must be at least 1';
        }

        if ($this->perPage < 1 || $this->perPage > 100) {
            $errors['perPage'] = 'Per page must be between 1 and 100';
        }

        // Validate sort order
        if (! in_array(strtolower($this->sortOrder), ['asc', 'desc'])) {
            $errors['sortOrder'] = 'Sort order must be asc or desc';
        }

        // Validate sort field
        if ($this->sortBy !== null && ! empty($allowedSortFields) && ! in_array($this->sortBy, $allowedSortFields)) {
            $errors['sortBy'] = 'Sort by must be one of: '.implode(', ', $allowedSortFields);
        }

        // Validate filters
        foreach ($this->filters as $filterKey => $value) {
            // Extract field name and operator (strip operator if present)
            $parts = explode(':', $filterKey);
            $field = $parts[0];
            $operator = strtolower($parts[1] ?? '='); // Normalize to lowercase

            // Validate operator
            $validOperators = ['=', 'in', 'gt', 'gte', 'lt', 'lte', 'like'];
            if (! in_array($operator, $validOperators)) {
                $errors["filters.{$filterKey}"] = "Filter operator '{$operator}' is not valid. Allowed: ".implode(', ', $validOperators);

                continue;
            }

            // Validate empty values
            if ($value === '' || $value === null || (is_array($value) && empty($value))) {
                $errors["filters.{$filterKey}"] = "Filter '{$field}' value cannot be empty";

                continue;
            }

            // Check if field is allowed
            if (! empty($allowedFilterFields) && ! isset($allowedFilterFields[$field])) {
                $errors["filters.{$filterKey}"] = "Filter field '{$field}' is not allowed";

                continue;
            }

            // Check if field has specific validation rules
            if (isset($allowedFilterFields[$field])) {
                $fieldRules = $allowedFilterFields[$field];

                // For 'in' operator, validate each value in the array
                if ($operator === 'in') {
                    $values = is_string($value) ? explode(',', $value) : (array) $value;
                    $values = array_map('trim', $values);

                    // Validate allowed values for 'in' operator
                    if (isset($fieldRules['allowed_values'])) {
                        foreach ($values as $val) {
                            if (! in_array($val, $fieldRules['allowed_values'])) {
                                $errors["filters.{$filterKey}"] = "Filter '{$field}' value '{$val}' must be one of: ".implode(', ', $fieldRules['allowed_values']);
                                break;
                            }
                        }
                    }

                    // Validate type for each value in 'in' operator
                    if (isset($fieldRules['type']) && ! isset($errors["filters.{$filterKey}"])) {
                        foreach ($values as $val) {
                            $valid = $this->validateType($val, $fieldRules['type']);
                            if (! $valid) {
                                $errors["filters.{$filterKey}"] = "Filter '{$field}' value '{$val}' must be of type {$fieldRules['type']}";
                                break;
                            }
                        }
                    }
                } else {
                    // For comparison operators, validate numeric values
                    if (in_array($operator, ['gt', 'gte', 'lt', 'lte']) && ! is_numeric($value)) {
                        $errors["filters.{$filterKey}"] = "Filter '{$field}' with operator '{$operator}' must have a numeric value";

                        continue;
                    }

                    // Validate allowed values (enum validation) for non-'in' operators
                    if (isset($fieldRules['allowed_values']) && ! in_array($value, $fieldRules['allowed_values'])) {
                        $errors["filters.{$filterKey}"] = "Filter '{$field}' must be one of: ".implode(', ', $fieldRules['allowed_values']);
                    }

                    // Validate type for single value operators
                    if (isset($fieldRules['type'])) {
                        $valid = $this->validateType($value, $fieldRules['type']);

                        if (! $valid) {
                            $errors["filters.{$filterKey}"] = "Filter '{$field}' must be of type {$fieldRules['type']}";
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get filters as associative array (for repository queries)
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Parse filters with operators into structured array
     * Returns: [['field' => 'status', 'operator' => 'in', 'value' => ['active', 'disabled']], ...]
     *
     * @return array Parsed filters
     */
    public function getParsedFilters(): array
    {
        $parsed = [];

        foreach ($this->filters as $key => $value) {
            // Parse field:operator format
            $parts = explode(':', $key);
            $field = $parts[0];
            $operator = $parts[1] ?? '=';

            // Normalize operator
            $operator = strtolower($operator);
            $validOperators = ['=', 'in', 'gt', 'gte', 'lt', 'lte', 'like'];
            if (! in_array($operator, $validOperators)) {
                $operator = '=';
            }

            // Parse value based on operator
            if ($operator === 'in') {
                // Split comma-separated values
                $value = is_string($value) ? explode(',', $value) : (array) $value;
                $value = array_map('trim', $value);
            }

            $parsed[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $value,
            ];
        }

        return $parsed;
    }

    /**
     * Get a specific filter value
     */
    public function getFilter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }

    /**
     * Check if a filter exists
     */
    public function hasFilter(string $key): bool
    {
        return isset($this->filters[$key]);
    }

    /**
     * Validate value against a specific type
     */
    private function validateType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'int' => is_int($value) || ctype_digit((string) $value),
            'float' => is_numeric($value),
            'bool' => is_bool($value) || in_array($value, ['true', 'false', '0', '1'], true),
            'date' => is_string($value) && $this->isValidDate($value),
            default => true,
        };
    }

    /**
     * Validate date string
     */
    private function isValidDate(string $date): bool
    {
        try {
            new \DateTimeImmutable($date);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get pagination offset
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * Get normalized sort order
     */
    public function getSortOrder(): string
    {
        return strtolower($this->sortOrder) === 'asc' ? 'asc' : 'desc';
    }
}
