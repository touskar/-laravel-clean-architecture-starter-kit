<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Entities\Session as SessionEntity;
use App\Infrastructure\Models\Session as SessionModel;

/**
 * SessionMapper - Eloquent Model ↔ Domain Entity
 */
class SessionMapper
{
    public function toDomain(?SessionModel $model): ?SessionEntity
    {
        if ($model === null) {
            return null;
        }

        return new SessionEntity(
            id: $model->id,
            userId: $model->user_id,
            token: null, // Never expose raw token from database
            hashedToken: $model->hashed_jwt,
            deviceName: $model->device_identifier,
            ipAddress: $model->last_ip,
            userAgent: $model->user_agent,
            isActive: $model->is_active,
            expiresAt: $model->expires_at ? \DateTimeImmutable::createFromMutable($model->expires_at) : null,
            lastUsedAt: $model->last_access_at ? \DateTimeImmutable::createFromMutable($model->last_access_at) : null,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at) : null
        );
    }

    public function toModel(SessionEntity $entity): SessionModel
    {
        $model = new SessionModel();
        $model->id = $entity->id;
        $model->user_id = $entity->userId;
        $model->hashed_jwt = $entity->hashedToken;
        $model->device_identifier = $entity->deviceName;
        $model->first_ip = $entity->ipAddress ?? '0.0.0.0';
        $model->last_ip = $entity->ipAddress ?? '0.0.0.0';
        $model->user_agent = $entity->userAgent;
        $model->is_active = $entity->isActive;
        $model->expires_at = $entity->expiresAt ? \DateTime::createFromImmutable($entity->expiresAt) : null;
        $model->last_access_at = $entity->lastUsedAt ? \DateTime::createFromImmutable($entity->lastUsedAt) : null;

        return $model;
    }
}
