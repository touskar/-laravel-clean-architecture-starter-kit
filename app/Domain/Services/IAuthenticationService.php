<?php

namespace App\Domain\Services;

use App\Domain\Entities\User;

/**
 * IAuthenticationService - Domain Service Interface
 * Prefix "I" indicates interface
 */
interface IAuthenticationService
{
    /**
     * Hash password using BCrypt (rounds=12)
     */
    public function hashPassword(string $password): string;

    /**
     * Verify password against hashed password
     */
    public function verifyPassword(string $password, string $hashedPassword): bool;

    /**
     * Create a new user session and return JWT token
     */
    public function createSession(User $user, ?string $deviceName = null, ?string $ipAddress = null, ?string $userAgent = null): array;

    /**
     * Get current authenticated user from token
     * Returns User with all nested relations
     */
    public function getCurrentUser(string $token): User;

    /**
     * Logout user (delete session by token)
     */
    public function logout(string $token): void;

    /**
     * Get current authenticated user from request bearer token
     * Automatically extracts token from global request()
     */
    public function getCurrentUserFromRequest(): User;
}
