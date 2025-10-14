<?php

namespace App\Domain\Services;

use App\Domain\Entities\User;

/**
 * IJwtService - Domain Service Interface
 * Prefix "I" indicates interface
 */
interface IJwtService
{
    /**
     * Generate JWT token for user
     * Returns array with 'token' and 'expiresIn' (seconds)
     */
    public function generateToken(User $user): array;

    /**
     * Parse and validate JWT token
     * Returns user ID from token
     * Throws exception if token is invalid or expired
     */
    public function validateToken(string $token): string;

    /**
     * Get user ID from token without validation
     */
    public function getUserIdFromToken(string $token): string;

    /**
     * Hash token for storage (BCrypt)
     */
    public function hashToken(string $token): string;

    /**
     * Verify token against hashed token
     */
    public function verifyHashedToken(string $token, string $hashedToken): bool;
}
