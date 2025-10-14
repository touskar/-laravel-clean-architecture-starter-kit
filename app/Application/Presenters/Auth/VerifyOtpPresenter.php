<?php

namespace App\Application\Presenters\Auth;

use App\Application\DTOs\Responses\VerifyOtpResponse;

/**
 * VerifyOtpPresenter - Formats response data
 */
class VerifyOtpPresenter
{
    private ?array $data = null;
    private ?string $error = null;

    public function present(VerifyOtpResponse $response): void
    {
        $message = $response->action === 'LOGIN'
            ? 'Bienvenue !'
            : 'Téléphone vérifié. Veuillez compléter votre inscription';

        $this->data = [
            'code' => 'OK',
            'success' => true,
            'message' => $message,
            'data' => $response->toArray(),
        ];
    }

    public function presentError(string $message): void
    {
        $this->error = $message;
        $this->data = [
            'code' => 'ERROR',
            'success' => false,
            'message' => $message,
            'data' => null,
        ];
    }

    public function getData(): array
    {
        return $this->data ?? [];
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }
}
