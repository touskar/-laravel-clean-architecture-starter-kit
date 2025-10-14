<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\Session;
use App\Domain\Entities\User;
use App\Domain\Repositories\ISessionRepository;
use App\Domain\Repositories\IUserRepository;
use App\Domain\Services\IAuthenticationService;
use App\Domain\Services\IJwtService;
use Symfony\Component\Uid\Ulid;

/**
 * AuthenticationServiceImpl - Service Implementation with "Impl" suffix
 */
class AuthenticationServiceImpl implements IAuthenticationService
{
    public function __construct(
        private readonly IJwtService $jwtService,
        private readonly IUserRepository $userRepository,
        private readonly ISessionRepository $sessionRepository
    ) {
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verifyPassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    public function createSession(
        User $user,
        ?string $deviceName = null,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): array {
        // Generate JWT token
        $tokenData = $this->jwtService->generateToken($user);
        $token = $tokenData['token'];
        $expiresIn = $tokenData['expiresIn'];

        // Hash the token for storage
        $hashedToken = $this->jwtService->hashToken($token);

        // Create session entity
        $session = new Session(
            id: (string) new Ulid(),
            userId: $user->id,
            token: $token,
            hashedToken: $hashedToken,
            deviceName: $deviceName,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
            expiresAt: new \DateTimeImmutable("+{$expiresIn} seconds"),
            lastUsedAt: new \DateTimeImmutable(),
            createdAt: new \DateTimeImmutable()
        );

        // Save session
        $this->sessionRepository->save($session);

        return [
            'token' => $token,
            'expiresIn' => $expiresIn,
        ];
    }

    public function getCurrentUser(string $token): User
    {
        // Validate token and get user ID
        $userId = $this->jwtService->validateToken($token);

        // Hash the token to find session in database
        $hashedToken = $this->jwtService->hashToken($token);

        // Check if session exists and is active
        $session = $this->sessionRepository->findByUserIdAndToken($userId, $hashedToken);
        if ($session === null) {
            throw new \RuntimeException('Session invalide ou expirée');
        }

        if (!$session->isActive) {
            throw new \RuntimeException('Session désactivée');
        }

        if ($session->isExpired()) {
            throw new \RuntimeException('Session expirée');
        }

        // Get user with all relations
        $user = $this->userRepository->findByIdWithRelations($userId);

        if ($user === null) {
            throw new \RuntimeException('Utilisateur non trouvé');
        }

        if (!$user->isActive()) {
            throw new \RuntimeException('Utilisateur inactif');
        }

        // Update last used timestamp
        $session->lastUsedAt = new \DateTimeImmutable();
        $this->sessionRepository->save($session);

        return $user;
    }

    public function logout(string $token): void
    {
        try {
            $userId = $this->jwtService->getUserIdFromToken($token);
            $hashedToken = $this->jwtService->hashToken($token);

            // Find and delete session
            $session = $this->sessionRepository->findByUserIdAndToken($userId, $hashedToken);
            if ($session !== null) {
                $this->sessionRepository->deleteById($session->id);
            }
        } catch (\Exception $e) {
            // Silently fail - token might be invalid or expired
        }
    }

    /**
     * Get current authenticated user from request bearer token
     *
     * @return User
     * @throws \RuntimeException if token is invalid or user not found
     */
    public function getCurrentUserFromRequest(): User
    {
        // Get token from global request helper
        $token = request()->bearerToken();

        if (!$token) {
            throw new \RuntimeException('Token manquant');
        }

        return $this->getCurrentUser($token);
    }
}
