<?php

namespace App\Domain\Entities;

use App\Domain\Traits\HasFilterableFields;

/**
 * User Domain Entity - Pure PHP POJO
 * No framework dependencies
 */
class User
{
    use HasFilterableFields;

    /**
     * Fields that can be searched (full-text search)
     */
    protected static array $searchableFields = [
        'name',
        'firstName',
        'lastName',
        'email',
        'username',
        'phoneNumber',
    ];

    /**
     * Fields that can be filtered with validation rules
     */
    protected static array $filterableFields = [
        'status' => [
            'type' => 'string',
            'allowed_values' => ['ACTIVE', 'INACTIVE', 'BANNED', 'PENDING'],
        ],
        'userType' => [
            'type' => 'string',
            'allowed_values' => ['USER', 'PLATFORM_ADMIN', 'CONTENT_CREATOR', 'ADVERTISER'],
        ],
    ];

    /**
     * Fields that can be used for sorting
     */
    protected static array $sortableFields = [
        'createdAt',
        'updatedAt',
        'name',
        'firstName',
        'lastName',
        'email',
        'username',
    ];

    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?string $username = null,
        public ?string $phoneNumber = null,
        public ?string $password = null,
        public ?string $address = null,
        public ?string $userType = null,
        public ?string $status = null,
        public ?\DateTimeImmutable $createdAt = null,
        public ?\DateTimeImmutable $updatedAt = null
    ) {}

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function isPlatformAdmin(): bool
    {
        return $this->userType === 'PLATFORM_ADMIN';
    }

    public function getFullName(): string
    {
        return trim(($this->firstName ?? '').' '.($this->lastName ?? ''));
    }
}
