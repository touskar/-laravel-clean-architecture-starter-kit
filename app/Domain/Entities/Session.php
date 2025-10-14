<?php

namespace App\Domain\Entities;

/**
 * Session Domain Entity - Pure PHP POJO
 * No framework dependencies
 */
class Session
{
    public function __construct(
        public ?string $id = null,
        public ?string $userId = null,
        public ?string $token = null,
        public ?string $hashedToken = null,
        public ?string $deviceName = null,
        public ?string $ipAddress = null,
        public ?string $userAgent = null,
        public bool $isActive = true,
        public ?\DateTimeImmutable $expiresAt = null,
        public ?\DateTimeImmutable $lastUsedAt = null,
        public ?\DateTimeImmutable $createdAt = null
    ) {
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return true;
        }
        return $this->expiresAt < new \DateTimeImmutable();
    }

    public function updateLastUsed(): void
    {
        $this->lastUsedAt = new \DateTimeImmutable();
    }
}
