<?php

namespace App\Application\Presenters\Auth;

use App\Application\DTOs\Responses\GetCurrentUserResponse;

/**
 * GetCurrentUserPresenter - Formats response data
 */
class GetCurrentUserPresenter
{
    private ?array $data = null;
    private ?string $error = null;

    public function present(GetCurrentUserResponse $response): void
    {
        $this->data = [
            'code' => 'OK',
            'success' => true,
            'message' => 'Utilisateur récupéré avec succès',
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
