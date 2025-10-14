<?php

namespace App\Application\Presenters\Auth;

use App\Application\DTOs\Responses\CompleteRegistrationResponse;

/**
 * CompleteRegistrationPresenter - Formats response data
 */
class CompleteRegistrationPresenter
{
    private ?array $data = null;
    private ?string $error = null;

    public function present(CompleteRegistrationResponse $response): void
    {
        $this->data = [
            'code' => 'OK',
            'success' => true,
            'message' => 'Inscription complétée avec succès',
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
