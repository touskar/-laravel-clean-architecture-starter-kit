<?php

namespace App\Application\DTOs\Requests;

/**
 * SendOtpRequest DTO
 */
class SendOtpRequest
{
    public function __construct(
        public readonly string $phoneNumber,
        public readonly string $callCode,
        public readonly string $userType
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            phoneNumber: $data['phoneNumber'] ?? $data['phone_number'] ?? '',
            callCode: $data['callCode'] ?? $data['call_code'] ?? '',
            userType: $data['userType'] ?? $data['user_type'] ?? ''
        );
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->phoneNumber)) {
            $errors['phoneNumber'] = 'Le numéro de téléphone est requis';
        } elseif (!preg_match('/^\d{9,15}$/', $this->phoneNumber)) {
            $errors['phoneNumber'] = 'Le numéro de téléphone doit contenir entre 9 et 15 chiffres';
        }

        if (empty($this->callCode)) {
            $errors['callCode'] = 'L\'indicatif est requis';
        } elseif (!preg_match('/^\d{1,4}$/', $this->callCode)) {
            $errors['callCode'] = 'L\'indicatif doit contenir entre 1 et 4 chiffres';
        }

        if (empty($this->userType)) {
            $errors['userType'] = 'Le type d\'utilisateur est requis';
        } elseif (!in_array($this->userType, ['ADVERTISER', 'CONTENT_CREATOR', 'PLATFORM_ADMIN'])) {
            $errors['userType'] = 'Le type d\'utilisateur doit être ADVERTISER, CONTENT_CREATOR ou PLATFORM_ADMIN';
        }

        return $errors;
    }
}
