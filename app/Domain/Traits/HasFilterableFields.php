<?php

namespace App\Domain\Traits;

/**
 * HasFilterableFields Trait - Domain Layer
 * Defines searchable and filterable fields at the domain level
 * This belongs in Domain because it defines business rules about which fields can be searched/filtered
 */
trait HasFilterableFields
{
    /**
     * Get searchable fields (for full-text search)
     * Override in domain entity to customize
     *
     * @return array Array of field names to search
     */
    public static function getSearchableFields(): array
    {
        return static::$searchableFields ?? [];
    }

    /**
     * Get filterable fields with validation rules
     * Override in domain entity to customize
     *
     * @return array Array of field => validation rules
     *
     * Example:
     * [
     *     'status' => [
     *         'type' => 'string',
     *         'allowed_values' => ['ACTIVE', 'DELETED', 'DISABLED']
     *     ],
     *     'campaign_status' => [
     *         'type' => 'string',
     *         'allowed_values' => ['OPEN', 'CLOSED', 'IN_REVIEW', 'REJECTED']
     *     ],
     *     'budget' => [
     *         'type' => 'float'
     *     ],
     *     'created_at' => [
     *         'type' => 'date'
     *     ]
     * ]
     */
    public static function getFilterableFields(): array
    {
        return static::$filterableFields ?? [];
    }

    /**
     * Get sortable fields
     * Override in domain entity to customize
     *
     * @return array Array of field names that can be used for sorting
     */
    public static function getSortableFields(): array
    {
        return static::$sortableFields ?? [];
    }
}
