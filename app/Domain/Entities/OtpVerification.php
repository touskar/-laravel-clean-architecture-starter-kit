<?php

namespace App\Domain\Entities;

/**
 * OtpVerification Domain Entity - Pure PHP POJO
 * No framework dependencies
 */
class OtpVerification
{
    public function __construct(
        public ?string $id = null,
        public ?string $sessionToken = null,
        public ?string $otpCode = null,
        public ?string $phoneNumber = null,
        public ?string $callCode = null,
        public ?string $countryCode = null,
        public ?string $email = null,
        public ?string $username = null,
        public ?string $password = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $address = null,
        public ?string $userType = null,
        public int $attemptCount = 0,
        public bool $verified = false,
        public ?\DateTimeImmutable $expiresAt = null,
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

    public function canAttempt(): bool
    {
        return $this->attemptCount < 3 && !$this->isExpired();
    }

    public function incrementAttempt(): void
    {
        $this->attemptCount++;
    }

    public function markAsVerified(): void
    {
        $this->verified = true;
    }

    public function getFullPhoneNumber(): string
    {
        return '+' . $this->callCode . $this->phoneNumber;
    }
}
