<?php

namespace App\Application\DTOs\Requests;

/**
 * VerifyOtpRequest DTO
 */
class VerifyOtpRequest
{
    public function __construct(
        public readonly string $sessionToken,
        public readonly string $otpCode,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sessionToken: $data['sessionToken'] ?? $data['session_token'] ?? '',
            otpCode: $data['otpCode'] ?? $data['otp_code'] ?? '',
            ipAddress: $data['ipAddress'] ?? $data['ip_address'] ?? null,
            userAgent: $data['userAgent'] ?? $data['user_agent'] ?? null
        );
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->sessionToken)) {
            $errors['sessionToken'] = 'Le jeton de session est requis';
        }

        if (empty($this->otpCode)) {
            $errors['otpCode'] = 'Le code OTP est requis';
        } elseif (!preg_match('/^\d{5}$/', $this->otpCode)) {
            $errors['otpCode'] = 'Le code OTP doit contenir exactement 5 chiffres';
        }

        return $errors;
    }
}
