<?php

namespace App\Application\DTOs\Shared;

use App\Domain\Entities\User;

/**
 * UserDto - Shared DTO for User data
 */
class UserDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $phone,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly string $userType = 'USER',
        public readonly string $status = 'ACTIVE'
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->id,
            username: $user->username,
            email: $user->email,
            phone: $user->phoneNumber,
            firstName: $user->firstName,
            lastName: $user->lastName,
            userType: $user->userType,
            status: $user->status
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'phone' => $this->phone,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'fullName' => trim(($this->firstName ?? '') . ' ' . ($this->lastName ?? '')),
            'userType' => $this->userType,
            'status' => $this->status,
        ];
    }
}
