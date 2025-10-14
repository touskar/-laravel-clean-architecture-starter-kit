<?php

namespace App\Application\Presenters\Auth;

use App\Application\DTOs\Responses\SendOtpResponse;

/**
 * SendOtpPresenter - Formats response data
 */
class SendOtpPresenter
{
    private ?array $data = null;
    private ?string $error = null;

    public function present(SendOtpResponse $response): void
    {
        $this->data = [
            'code' => 'OK',
            'success' => true,
            'message' => 'Code de vérification envoyé avec succès',
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
