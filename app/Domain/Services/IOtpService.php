<?php

namespace App\Domain\Services;

use App\Domain\Entities\OtpVerification;

/**
 * IOtpService - Domain Service Interface
 * Prefix "I" indicates interface
 */
interface IOtpService
{
    /**
     * Generate a 5-digit OTP code
     */
    public function generateOtpCode(): string;

    /**
     * Generate a unique session token (ULID)
     */
    public function generateSessionToken(): string;

    /**
     * Create and save a new OTP verification
     * Returns OtpVerification with sessionToken, expiresAt set
     */
    public function createOtpVerification(string $phoneNumber, string $callCode, string $userType): OtpVerification;

    /**
     * Verify OTP code against session token
     * Throws exception if invalid, expired, or max attempts exceeded
     */
    public function verifyOtp(string $sessionToken, string $otpCode): OtpVerification;

    /**
     * Send OTP via SMS (or print to console for development)
     */
    public function sendOtp(string $phoneNumber, string $callCode, string $otpCode): void;
}
