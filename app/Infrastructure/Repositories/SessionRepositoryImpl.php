<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\Session;
use App\Domain\Repositories\ISessionRepository;
use App\Infrastructure\Mappers\SessionMapper;
use App\Infrastructure\Models\Session as SessionModel;

/**
 * SessionRepositoryImpl - Implementation with "Impl" suffix
 * Bridges Domain and Infrastructure
 */
class SessionRepositoryImpl implements ISessionRepository
{
    public function __construct(
        private readonly SessionMapper $mapper
    ) {
    }

    public function save(Session $session): Session
    {
        // Check if session exists to determine if UPDATE or INSERT
        $existingModel = SessionModel::find($session->id);

        if ($existingModel) {
            // UPDATE existing session
            $existingModel->user_id = $session->userId;
            $existingModel->hashed_jwt = $session->hashedToken;
            $existingModel->device_identifier = $session->deviceName;
            $existingModel->last_ip = $session->ipAddress ?? '0.0.0.0';
            $existingModel->user_agent = $session->userAgent;
            $existingModel->is_active = $session->isActive;
            $existingModel->expires_at = $session->expiresAt ? \DateTime::createFromImmutable($session->expiresAt) : null;
            $existingModel->last_access_at = $session->lastUsedAt ? \DateTime::createFromImmutable($session->lastUsedAt) : null;
            $existingModel->save();

            return $this->mapper->toDomain($existingModel);
        } else {
            // INSERT new session
            $model = $this->mapper->toModel($session);
            $model->save();

            return $this->mapper->toDomain($model);
        }
    }

    public function findById(string $id): ?Session
    {
        $model = SessionModel::find($id);
        return $this->mapper->toDomain($model);
    }

    public function findByUserIdAndToken(string $userId, string $hashedToken): ?Session
    {
        // hashed_jwt uses MD5 for fast lookup
        $model = SessionModel::where('user_id', $userId)
            ->where('hashed_jwt', $hashedToken)
            ->first();

        return $this->mapper->toDomain($model);
    }

    public function findByUserId(string $userId): array
    {
        $models = SessionModel::where('user_id', $userId)->get();
        return $models->map(fn($model) => $this->mapper->toDomain($model))->all();
    }

    public function deleteById(string $id): void
    {
        SessionModel::where('id', $id)->delete();
    }

    public function deleteByUserId(string $userId): void
    {
        SessionModel::where('user_id', $userId)->delete();
    }

    public function deleteExpired(): int
    {
        return SessionModel::where('expires_at', '<', now())->delete();
    }
}
