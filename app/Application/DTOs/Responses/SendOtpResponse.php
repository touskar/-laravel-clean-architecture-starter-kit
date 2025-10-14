<?php

namespace App\Application\DTOs\Responses;

/**
 * SendOtpResponse DTO
 */
class SendOtpResponse
{
    public function __construct(
        public readonly string $sessionToken,
        public readonly string $phoneNumber,
        public readonly int $expiresIn
    ) {
    }

    public function toArray(): array
    {
        return [
            'sessionToken' => $this->sessionToken,
            'phoneNumber' => $this->phoneNumber,
            'expiresIn' => $this->expiresIn,
        ];
    }
}
