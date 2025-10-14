<?php

namespace App\Application\DTOs\Shared;

use App\Domain\Entities\User;

/**
 * UserDto - Shared DTO for User data
 */
class UserDto
{
    public function __construct(
        public readonly string $userId,
        public readonly string $username,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $name,
        public readonly string $phoneNumber,
        public readonly ?string $email,
        public readonly string $userType,
        public readonly ?string $status = null,
        public readonly ?AdvertiserCompanyDto $advertiserCompany = null,
        public readonly ?ContentCreatorDto $contentCreator = null
    ) {
    }

    public static function fromEntity(User $user): self
    {
        return new self(
            userId: $user->id,
            username: $user->username,
            firstName: $user->firstName,
            lastName: $user->lastName,
            name: $user->name,
            phoneNumber: $user->phoneNumber,
            email: $user->email,
            userType: $user->userType,
            status: $user->status,
            advertiserCompany: $user->advertiserCompany ? AdvertiserCompanyDto::fromEntity($user->advertiserCompany) : null,
            contentCreator: $user->contentCreator ? ContentCreatorDto::fromEntity($user->contentCreator) : null
        );
    }

    public function toArray(): array
    {
        $data = [
            'userId' => $this->userId,
            'username' => $this->username,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'name' => $this->name,
            'phoneNumber' => $this->phoneNumber,
            'email' => $this->email,
            'userType' => $this->userType,
        ];

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        $data['advertiserCompany'] = $this->advertiserCompany?->toArray();
        $data['contentCreator'] = $this->contentCreator?->toArray();

        return $data;
    }
}
