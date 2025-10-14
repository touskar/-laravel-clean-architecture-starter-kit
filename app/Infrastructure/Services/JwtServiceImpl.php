<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\User;
use App\Domain\Services\IJwtService;
use App\Infrastructure\Models\User as UserModel;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

/**
 * JwtServiceImpl - Service Implementation with "Impl" suffix
 * Uses php-open-source-saver/jwt-auth
 */
class JwtServiceImpl implements IJwtService
{
    private const TOKEN_EXPIRATION_SECONDS = 86400; // 1 day (24 hours)

    public function generateToken(User $user): array
    {
        // Fetch the Eloquent model (required by JWT library)
        $userModel = UserModel::find($user->id);

        if (!$userModel) {
            throw new \RuntimeException('Utilisateur introuvable');
        }

        // Generate JWT using Eloquent model (implements JWTSubject)
        $token = JWTAuth::fromUser($userModel);

        return [
            'token' => $token,
            'expiresIn' => self::TOKEN_EXPIRATION_SECONDS,
        ];
    }

    public function validateToken(string $token): string
    {
        try {
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();

            return $payload->get('sub');
        } catch (JWTException $e) {
            throw new \RuntimeException('Token invalide ou expiré');
        }
    }

    public function getUserIdFromToken(string $token): string
    {
        try {
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();

            return $payload->get('sub');
        } catch (JWTException $e) {
            throw new \RuntimeException('Token invalide');
        }
    }

    public function hashToken(string $token): string
    {
        // Use MD5 for fast token lookup (not for password security)
        return md5($token);
    }

    public function verifyHashedToken(string $token, string $hashedToken): bool
    {
        return md5($token) === $hashedToken;
    }
}
