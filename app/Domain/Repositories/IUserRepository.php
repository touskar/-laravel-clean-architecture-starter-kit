<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\User;

/**
 * IUserRepository - Domain Repository Interface
 * Prefix "I" indicates interface
 */
interface IUserRepository
{
    /**
     * Save or update a user
     */
    public function save(User $user): User;

    /**
     * Find user by ID
     */
    public function findById(string $id): ?User;

    /**
     * Find user by phone number
     */
    public function findByPhoneNumber(string $phoneNumber): ?User;

    /**
     * Find user by phone number and user type
     * (same phone can be used by different user types: ADVERTISER, CONTENT_CREATOR)
     */
    public function findByPhoneNumberAndUserType(string $phoneNumber, string $userType): ?User;

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?User;

    /**
     * Check if phone number exists
     */
    public function existsByPhoneNumber(string $phoneNumber): bool;

    /**
     * Check if email exists
     */
    public function existsByEmail(string $email): bool;

    /**
     * Check if username exists
     */
    public function existsByUsername(string $username): bool;

    /**
     * Get user with all nested relations (contentCreator, advertiserCompany)
     */
    public function findByIdWithRelations(string $id): ?User;
}
