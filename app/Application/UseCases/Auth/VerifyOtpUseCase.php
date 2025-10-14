<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Requests\VerifyOtpRequest;
use App\Application\DTOs\Responses\VerifyOtpResponse;
use App\Application\DTOs\Shared\UserDto;
use App\Application\Presenters\Auth\VerifyOtpPresenter;
use App\Domain\Repositories\IUserRepository;
use App\Domain\Services\IAuthenticationService;
use App\Domain\Services\IOtpService;

/**
 * VerifyOtpUseCase - Business logic for verifying OTP
 */
class VerifyOtpUseCase
{
    public function __construct(
        private readonly VerifyOtpPresenter $presenter,
        private readonly IOtpService $otpService,
        private readonly IUserRepository $userRepository,
        private readonly IAuthenticationService $authService
    ) {
    }

    public function execute(VerifyOtpRequest $request): void
    {
        try {
            // Validate request
            $errors = $request->validate();
            if (!empty($errors)) {
                $errorMessage = implode(', ', $errors);
                $this->presenter->presentError($errorMessage);
                return;
            }

            // Start transaction for write operations
            \DB::beginTransaction();

            // Verify OTP
            $otpVerification = $this->otpService->verifyOtp(
                $request->sessionToken,
                $request->otpCode
            );

            // Check if user exists with this phone number
            $fullPhoneNumber = $otpVerification->getFullPhoneNumber();
            $existingUser = $this->userRepository->findByPhoneNumber($fullPhoneNumber);

            if ($existingUser !== null) {
                // LOGIN flow - user exists
                // Get user with all relations
                $user = $this->userRepository->findByIdWithRelations($existingUser->id);

                // Create session and JWT with IP address and user agent
                $sessionData = $this->authService->createSession(
                    $user,
                    null,  // deviceName
                    $request->ipAddress,  // ipAddress
                    $request->userAgent   // userAgent
                );

                $response = new VerifyOtpResponse(
                    action: 'LOGIN',
                    token: $sessionData['token'],
                    expiresIn: $sessionData['expiresIn'],
                    user: UserDto::fromEntity($user)
                );
            } else {
                // REGISTER flow - new user
                $response = new VerifyOtpResponse(
                    action: 'REGISTER',
                    sessionToken: $otpVerification->sessionToken,
                    phoneNumber: $fullPhoneNumber,
                    verified: true
                );
            }

            // Commit transaction
            \DB::commit();

            $this->presenter->present($response);
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('VerifyOtpUseCase Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->presenter->presentError($e->getMessage());
        }
    }
}
