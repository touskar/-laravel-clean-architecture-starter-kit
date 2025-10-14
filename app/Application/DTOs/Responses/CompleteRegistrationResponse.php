<?php

namespace App\Application\DTOs\Responses;

use App\Application\DTOs\Shared\UserDto;

/**
 * CompleteRegistrationResponse DTO
 */
class CompleteRegistrationResponse
{
    public function __construct(
        public readonly string $token,
        public readonly int $expiresIn,
        public readonly UserDto $user
    ) {
    }

    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'expiresIn' => $this->expiresIn,
            'user' => $this->user->toArray(),
        ];
    }
}
