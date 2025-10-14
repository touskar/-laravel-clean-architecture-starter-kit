<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\OtpVerification;
use App\Domain\Repositories\IOtpVerificationRepository;
use App\Infrastructure\Mappers\OtpVerificationMapper;
use App\Infrastructure\Models\OtpVerification as OtpVerificationModel;

/**
 * OtpVerificationRepositoryImpl - Implementation with "Impl" suffix
 * Bridges Domain and Infrastructure
 */
class OtpVerificationRepositoryImpl implements IOtpVerificationRepository
{
    public function __construct(
        private readonly OtpVerificationMapper $mapper
    ) {
    }

    public function save(OtpVerification $otpVerification): OtpVerification
    {
        $model = $this->mapper->toModel($otpVerification);
        $model->save();

        return $this->mapper->toDomain($model);
    }

    public function findBySessionToken(string $sessionToken): ?OtpVerification
    {
        $model = OtpVerificationModel::where('session_token', $sessionToken)->first();
        return $this->mapper->toDomain($model);
    }

    public function findByPhoneNumber(string $callCode, string $phoneNumber): ?OtpVerification
    {
        $model = OtpVerificationModel::where('call_code', $callCode)
            ->where('phone_number', $phoneNumber)
            ->orderBy('created_at', 'desc')
            ->first();

        return $this->mapper->toDomain($model);
    }

    public function deleteBySessionToken(string $sessionToken): void
    {
        OtpVerificationModel::where('session_token', $sessionToken)->delete();
    }

    public function deleteExpired(): int
    {
        return OtpVerificationModel::where('expires_at', '<', now())->delete();
    }
}
