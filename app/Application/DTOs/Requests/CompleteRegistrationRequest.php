<?php

namespace App\Application\DTOs\Requests;

/**
 * CompleteRegistrationRequest DTO
 */
class CompleteRegistrationRequest
{
    public function __construct(
        public readonly string $sessionToken,
        public readonly string $username,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly string $countryCode,
        public readonly string $userType,
        public readonly ?string $password = null,
        public readonly ?string $address = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            sessionToken: $data['sessionToken'] ?? $data['session_token'] ?? '',
            username: $data['username'] ?? '',
            firstName: $data['firstName'] ?? $data['first_name'] ?? '',
            lastName: $data['lastName'] ?? $data['last_name'] ?? '',
            email: $data['email'] ?? '',
            countryCode: $data['countryCode'] ?? $data['country_code'] ?? '',
            userType: $data['userType'] ?? $data['user_type'] ?? '',
            password: $data['password'] ?? null,
            address: $data['address'] ?? null,
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

        if (empty($this->username)) {
            $errors['username'] = 'Le nom d\'utilisateur est requis';
        } elseif (strlen($this->username) < 3 || strlen($this->username) > 50) {
            $errors['username'] = 'Le nom d\'utilisateur doit contenir entre 3 et 50 caractères';
        }

        if (empty($this->firstName)) {
            $errors['firstName'] = 'Le prénom est requis';
        }

        if (empty($this->lastName)) {
            $errors['lastName'] = 'Le nom est requis';
        }

        // Password is optional for OTP-based authentication
        if (!empty($this->password) && !$this->isValidPassword($this->password)) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial';
        }

        if (empty($this->email)) {
            $errors['email'] = 'L\'email est requis';
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'L\'email n\'est pas valide';
        }

        if (empty($this->countryCode)) {
            $errors['countryCode'] = 'Le code pays est requis';
        }

        if (empty($this->userType)) {
            $errors['userType'] = 'Le type d\'utilisateur est requis';
        } elseif (!in_array($this->userType, ['ADVERTISER', 'CONTENT_CREATOR'])) {
            $errors['userType'] = 'Le type d\'utilisateur doit être ADVERTISER ou CONTENT_CREATOR';
        }

        return $errors;
    }

    private function isValidPassword(string $password): bool
    {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 digit, 1 special char
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password)
            && preg_match('/[@$!%*?&]/', $password);
    }
}
