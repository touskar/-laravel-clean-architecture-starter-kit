<?php

namespace App\Domain\Repositories;

use App\Domain\Entities\OtpVerification;

/**
 * IOtpVerificationRepository - Domain Repository Interface
 * Prefix "I" indicates interface
 */
interface IOtpVerificationRepository
{
    /**
     * Save or update an OTP verification
     */
    public function save(OtpVerification $otpVerification): OtpVerification;

    /**
     * Find OTP verification by session token
     */
    public function findBySessionToken(string $sessionToken): ?OtpVerification;

    /**
     * Find OTP verification by phone number
     */
    public function findByPhoneNumber(string $callCode, string $phoneNumber): ?OtpVerification;

    /**
     * Delete OTP verification by session token
     */
    public function deleteBySessionToken(string $sessionToken): void;

    /**
     * Delete expired OTP verifications
     */
    public function deleteExpired(): int;
}
