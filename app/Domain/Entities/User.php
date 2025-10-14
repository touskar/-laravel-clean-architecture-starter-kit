<?php

namespace App\Domain\Entities;

/**
 * User Domain Entity - Pure PHP POJO
 * No framework dependencies
 */
class User
{
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
    ) {
    }

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
        return trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? ''));
    }
}
