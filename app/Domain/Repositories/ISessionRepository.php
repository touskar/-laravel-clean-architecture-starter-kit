<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\Session;

/**
 * ISessionRepository - Domain Repository Interface
 * Prefix "I" indicates interface
 */
interface ISessionRepository
{
    /**
     * Save or update a session
     */
    public function save(Session $session): Session;

    /**
     * Find session by ID
     */
    public function findById(string $id): ?Session;

    /**
     * Find session by user ID and token
     */
    public function findByUserIdAndToken(string $userId, string $token): ?Session;

    /**
     * Find all sessions for a user
     */
    public function findByUserId(string $userId): array;

    /**
     * Delete session by ID
     */
    public function deleteById(string $id): void;

    /**
     * Delete all sessions for a user
     */
    public function deleteByUserId(string $userId): void;

    /**
     * Delete expired sessions
     */
    public function deleteExpired(): int;
}
