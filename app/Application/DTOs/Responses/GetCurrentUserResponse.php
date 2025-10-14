<?php

namespace App\Application\DTOs\Responses;

use App\Application\DTOs\Shared\UserDto;

/**
 * GetCurrentUserResponse DTO
 */
class GetCurrentUserResponse
{
    public function __construct(
        public readonly UserDto $user
    ) {
    }

    public function toArray(): array
    {
        return $this->user->toArray();
    }
}
