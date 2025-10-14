<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Responses\GetCurrentUserResponse;
use App\Application\DTOs\Shared\UserDto;
use App\Application\Presenters\Auth\GetCurrentUserPresenter;
use App\Domain\Services\IAuthenticationService;

/**
 * GetCurrentUserUseCase - Business logic for getting current user
 */
class GetCurrentUserUseCase
{
    public function __construct(
        private readonly GetCurrentUserPresenter $presenter,
        private readonly IAuthenticationService $authService
    ) {
    }

    public function execute(): void
    {
        try {
            // Get current user from request bearer token (includes validation)
            $user = $this->authService->getCurrentUserFromRequest();

            // Build response
            $response = new GetCurrentUserResponse(
                user: UserDto::fromEntity($user)
            );

            $this->presenter->present($response);
        } catch (\Exception $e) {
            \Log::error('[GetCurrentUserUseCase] Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->presenter->presentError($e->getMessage());
        }
    }
}
