<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Entities\OtpVerification as OtpVerificationEntity;
use App\Infrastructure\Models\OtpVerification as OtpVerificationModel;

/**
 * OtpVerificationMapper - Eloquent Model ↔ Domain Entity
 */
class OtpVerificationMapper
{
    public function toDomain(?OtpVerificationModel $model): ?OtpVerificationEntity
    {
        if ($model === null) {
            return null;
        }

        return new OtpVerificationEntity(
            id: $model->id,
            sessionToken: $model->session_token,
            otpCode: $model->otp_code,
            phoneNumber: $model->phone_number,
            callCode: $model->call_code,
            countryCode: $model->country_code,
            email: $model->email,
            username: $model->username,
            password: $model->password,
            firstName: $model->first_name,
            lastName: $model->last_name,
            address: $model->address,
            userType: $model->user_type,
            attemptCount: $model->attempt_count ?? 0,
            verified: $model->verified ?? false,
            expiresAt: $model->expires_at ? \DateTimeImmutable::createFromMutable($model->expires_at) : null,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at) : null
        );
    }

    public function toModel(OtpVerificationEntity $entity): OtpVerificationModel
    {
        // Try to find existing model by ID (for updates)
        $model = $entity->id ? OtpVerificationModel::find($entity->id) : null;

        // If not found, create new model
        if ($model === null) {
            $model = new OtpVerificationModel();
            $model->id = $entity->id;
        }

        $model->session_token = $entity->sessionToken;
        $model->otp_code = $entity->otpCode;
        $model->phone_number = $entity->phoneNumber;
        $model->call_code = $entity->callCode;
        $model->country_code = $entity->countryCode;
        $model->email = $entity->email;
        $model->username = $entity->username;
        $model->password = $entity->password;
        $model->first_name = $entity->firstName;
        $model->last_name = $entity->lastName;
        $model->address = $entity->address;
        $model->user_type = $entity->userType;
        $model->attempt_count = $entity->attemptCount;
        $model->verified = $entity->verified;
        $model->expires_at = $entity->expiresAt ? \DateTime::createFromImmutable($entity->expiresAt) : null;

        return $model;
    }
}
