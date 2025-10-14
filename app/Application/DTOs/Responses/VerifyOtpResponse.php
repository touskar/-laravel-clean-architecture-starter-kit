<?php

namespace App\Application\DTOs\Responses;

use App\Application\DTOs\Shared\UserDto;

/**
 * VerifyOtpResponse DTO
 */
class VerifyOtpResponse
{
    public function __construct(
        public readonly string $action, // "LOGIN" or "REGISTER"
        public readonly ?string $token = null,
        public readonly ?int $expiresIn = null,
        public readonly ?UserDto $user = null,
        public readonly ?string $sessionToken = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?bool $verified = null
    ) {
    }

    public function toArray(): array
    {
        $data = [
            'action' => $this->action,
        ];

        if ($this->token !== null) {
            $data['token'] = $this->token;
            $data['expiresIn'] = $this->expiresIn;
            $data['user'] = $this->user->toArray();
        } else {
            $data['sessionToken'] = $this->sessionToken;
            $data['phoneNumber'] = $this->phoneNumber;
            $data['verified'] = $this->verified;
        }

        return $data;
    }
}
