<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Requests\CompleteRegistrationRequest;
use App\Application\DTOs\Responses\CompleteRegistrationResponse;
use App\Application\DTOs\Shared\UserDto;
use App\Application\Presenters\Auth\CompleteRegistrationPresenter;
use App\Domain\Entities\User;
use App\Domain\Repositories\ICountryRepository;
use App\Domain\Repositories\IOtpVerificationRepository;
use App\Domain\Repositories\IUserRepository;
use App\Domain\Services\IAuthenticationService;
use Symfony\Component\Uid\Ulid;

/**
 * CompleteRegistrationUseCase - Business logic for completing registration
 */
class CompleteRegistrationUseCase
{
    public function __construct(
        private readonly CompleteRegistrationPresenter $presenter,
        private readonly IOtpVerificationRepository $otpRepository,
        private readonly IUserRepository $userRepository,
        private readonly ICountryRepository $countryRepository,
        private readonly IAuthenticationService $authService
    ) {
    }

    public function execute(CompleteRegistrationRequest $request): void
    {
        try {
            // Validate request
            $errors = $request->validate();
            if (!empty($errors)) {
                $errorMessage = implode(', ', $errors);
                $this->presenter->presentError($errorMessage);
                return;
            }

            // Find OTP verification
            $otpVerification = $this->otpRepository->findBySessionToken($request->sessionToken);
            if ($otpVerification === null) {
                $this->presenter->presentError('Session invalide');
                return;
            }

            if (!$otpVerification->verified) {
                $this->presenter->presentError('Téléphone non vérifié');
                return;
            }

            // Check if user already exists
            $fullPhoneNumber = $otpVerification->getFullPhoneNumber();
            if ($this->userRepository->existsByPhoneNumber($fullPhoneNumber)) {
                $this->presenter->presentError('Un utilisateur avec ce numéro de téléphone existe déjà');
                return;
            }

            if ($this->userRepository->existsByUsername($request->username)) {
                $this->presenter->presentError('Ce nom d\'utilisateur est déjà utilisé');
                return;
            }

            if ($this->userRepository->existsByEmail($request->email)) {
                $this->presenter->presentError('Cet email est déjà utilisé');
                return;
            }

            // Find country
            $country = $this->countryRepository->findByCode($request->countryCode);
            if ($country === null) {
                $this->presenter->presentError('Pays invalide');
                return;
            }

            // Start transaction
            \DB::beginTransaction();

            // Hash password - if not provided, generate a random one (OTP-based auth)
            $password = $request->password ?? bin2hex(random_bytes(16));
            $hashedPassword = $this->authService->hashPassword($password);

            // Create user entity
            $user = new User(
                id: (string) new Ulid(),
                name: $request->firstName . ' ' . $request->lastName,
                firstName: $request->firstName,
                lastName: $request->lastName,
                email: $request->email,
                username: $request->username,
                phoneNumber: $fullPhoneNumber,
                password: $hashedPassword,
                address: $request->address,
                userType: $request->userType,
                status: 'ACTIVE',
                country: $country,
                createdAt: new \DateTimeImmutable(),
                updatedAt: new \DateTimeImmutable()
            );

            // Save user
            $savedUser = $this->userRepository->save($user);

            // Delete OTP verification (no longer needed)
            $this->otpRepository->deleteBySessionToken($request->sessionToken);

            // Get user with all relations
            $userWithRelations = $this->userRepository->findByIdWithRelations($savedUser->id);

            // Create session and JWT with IP address and user agent
            $sessionData = $this->authService->createSession(
                $userWithRelations,
                null,  // deviceName
                $request->ipAddress,  // ipAddress
                $request->userAgent   // userAgent
            );

            // Commit transaction
            \DB::commit();

            // Build response
            $response = new CompleteRegistrationResponse(
                token: $sessionData['token'],
                expiresIn: $sessionData['expiresIn'],
                user: UserDto::fromEntity($userWithRelations)
            );

            $this->presenter->present($response);
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('CompleteRegistrationUseCase Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->presenter->presentError($e->getMessage());
        }
    }
}
