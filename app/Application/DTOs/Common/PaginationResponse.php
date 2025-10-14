<?php

namespace App\Application\DTOs\Common;

/**
 * PaginationResponse - Reusable pagination metadata
 * Includes has_more for infinite scroll support
 */
class PaginationResponse
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $totalPages,
        public readonly bool $hasMore,
        public readonly bool $hasPrevious
    ) {}

    /**
     * Create from repository result
     */
    public static function fromRepositoryResult(array $result): self
    {
        return new self(
            currentPage: $result['currentPage'],
            perPage: $result['perPage'],
            total: $result['total'],
            totalPages: $result['totalPages'],
            hasMore: $result['hasNextPage'] ?? $result['hasMore'] ?? false,
            hasPrevious: $result['hasPreviousPage'] ?? $result['hasPrevious'] ?? false
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
            'total' => $this->total,
            'totalPages' => $this->totalPages,
            'hasMore' => $this->hasMore,
            'hasPrevious' => $this->hasPrevious,
        ];
    }
}
