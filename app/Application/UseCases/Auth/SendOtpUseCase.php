<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Requests\SendOtpRequest;
use App\Application\DTOs\Responses\SendOtpResponse;
use App\Application\Presenters\Auth\SendOtpPresenter;
use App\Domain\Repositories\IUserRepository;
use App\Domain\Services\IOtpService;

/**
 * SendOtpUseCase - Business logic for sending OTP
 */
class SendOtpUseCase
{
    public function __construct(
        private readonly SendOtpPresenter $presenter,
        private readonly IOtpService $otpService,
        private readonly IUserRepository $userRepository
    ) {
    }

    public function execute(SendOtpRequest $request): void
    {
        try {
            // Validate request
            $errors = $request->validate();
            if (!empty($errors)) {
                $errorMessage = implode(', ', $errors);
                $this->presenter->presentError($errorMessage);
                return;
            }

            // Build full phone number for lookup
            $fullPhoneNumber = '+' . $request->callCode . $request->phoneNumber;

            // Check if user already exists with this phone number
            $existingUser = $this->userRepository->findByPhoneNumber($fullPhoneNumber);

            if ($existingUser !== null) {
                // User exists - verify userType matches
                if ($existingUser->userType !== $request->userType) {
                    // Map userType to French labels
                    $userTypeLabels = [
                        'CONTENT_CREATOR' => 'créateur de contenu',
                        'ADVERTISER' => 'annonceur',
                        'PLATFORM_ADMIN' => 'administrateur'
                    ];
                    $userTypeLabel = $userTypeLabels[$existingUser->userType] ?? $existingUser->userType;

                    $this->presenter->presentError(
                        "Ce numéro de téléphone est enregistré pour un compte {$userTypeLabel}. " .
                        "Veuillez utiliser l'application correspondante."
                    );
                    return;
                }
            }

            // Start transaction for write operations
            \DB::beginTransaction();

            // User doesn't exist (registration flow) OR user exists with matching userType (login flow)
            // Create OTP verification with userType
            $otpVerification = $this->otpService->createOtpVerification(
                $request->phoneNumber,
                $request->callCode,
                $request->userType
            );

            // Commit transaction
            \DB::commit();

            // Calculate expiration in seconds
            $now = new \DateTimeImmutable();
            $expiresIn = $otpVerification->expiresAt->getTimestamp() - $now->getTimestamp();

            // Build response
            $response = new SendOtpResponse(
                sessionToken: $otpVerification->sessionToken,
                phoneNumber: $otpVerification->getFullPhoneNumber(),
                expiresIn: $expiresIn
            );

            $this->presenter->present($response);
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('SendOtpUseCase Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->presenter->presentError($e->getMessage());
        }
    }
}
