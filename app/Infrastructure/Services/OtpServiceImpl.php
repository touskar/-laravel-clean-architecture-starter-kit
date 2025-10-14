<?php

namespace App\Infrastructure\Services;

use App\Domain\Entities\OtpVerification;
use App\Domain\Repositories\IOtpVerificationRepository;
use App\Domain\Services\IOtpService;
use App\Domain\Services\IRandomStringService;
use Symfony\Component\Uid\Ulid;

/**
 * OtpServiceImpl - Service Implementation with "Impl" suffix
 */
class OtpServiceImpl implements IOtpService
{
    public function __construct(
        private readonly IOtpVerificationRepository $otpRepository,
        private readonly IRandomStringService $randomStringService
    ) {
    }

    public function generateOtpCode(): string
    {
        // Generate 5-digit random number using RandomStringService
        return $this->randomStringService->generateNumber(5);
    }

    public function generateSessionToken(): string
    {
        // Generate truly random hex token (32 bytes = 64 hex characters)
        // This doesn't contain any timestamp or predictable information
        return $this->randomStringService->generateHex(32);
    }

    public function createOtpVerification(string $phoneNumber, string $callCode, string $userType): OtpVerification
    {
        $otpCode = $this->generateOtpCode();
        $sessionToken = $this->generateSessionToken();

        $otpVerification = new OtpVerification(
            id: (string) new Ulid(),
            sessionToken: $sessionToken,
            otpCode: $otpCode,
            phoneNumber: $phoneNumber,
            callCode: $callCode,
            userType: $userType,
            attemptCount: 0,
            verified: false,
            expiresAt: new \DateTimeImmutable('+5 minutes'),
            createdAt: new \DateTimeImmutable()
        );

        $saved = $this->otpRepository->save($otpVerification);

        // Send OTP
        $this->sendOtp($phoneNumber, $callCode, $otpCode);

        return $saved;
    }

    public function verifyOtp(string $sessionToken, string $otpCode): OtpVerification
    {
        $otpVerification = $this->otpRepository->findBySessionToken($sessionToken);

        if ($otpVerification === null) {
            throw new \RuntimeException('Session invalide');
        }

        if ($otpVerification->isExpired()) {
            throw new \RuntimeException('Le code OTP a expiré');
        }

        if (!$otpVerification->canAttempt()) {
            throw new \RuntimeException('Nombre maximum de tentatives atteint');
        }

        // Increment attempt count
        $otpVerification->incrementAttempt();
        $this->otpRepository->save($otpVerification);

        if ($otpVerification->otpCode !== $otpCode) {
            throw new \RuntimeException('Code de vérification invalide');
        }

        // Mark as verified
        $otpVerification->markAsVerified();
        return $this->otpRepository->save($otpVerification);
    }

    public function sendOtp(string $phoneNumber, string $callCode, string $otpCode): void
    {
        $fullPhone = '+' . $callCode . $phoneNumber;

        // Log fake SMS (for development - doesn't break JSON response)
        \Log::info('📱 FAKE SMS SERVICE - Single SMS', [
            'from' => 'RSMARKETING',
            'to' => $fullPhone,
            'message' => "Your verification code is: $otpCode"
        ]);

        // In production, integrate with real SMS provider (Twilio, AWS SNS, etc.)
        // Example:
        // $this->smsProvider->send($fullPhone, "Your verification code is: $otpCode");
    }
}
