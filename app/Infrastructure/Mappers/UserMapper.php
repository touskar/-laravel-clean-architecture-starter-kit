<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Entities\User as UserEntity;
use App\Infrastructure\Models\User as UserModel;

/**
 * UserMapper - Eloquent Model ↔ Domain Entity
 */
class UserMapper
{
    public static function toDomain(?UserModel $model): ?UserEntity
    {
        if ($model === null) {
            return null;
        }

        return new UserEntity(
            id: $model->id,
            name: $model->name,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            username: $model->username,
            phoneNumber: $model->phone,
            password: $model->password_hash,
            address: $model->address ?? null,
            userType: $model->user_type,
            status: $model->status,
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at) : null,
            updatedAt: $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at) : null
        );
    }

    public static function toModel(UserEntity $entity): UserModel
    {
        $model = new UserModel();
        $model->id = $entity->id;
        $model->name = $entity->name;
        $model->first_name = $entity->firstName;
        $model->last_name = $entity->lastName;
        $model->email = $entity->email;
        $model->username = $entity->username;
        $model->phone = $entity->phoneNumber;
        $model->password_hash = $entity->password;
        $model->address = $entity->address;
        $model->user_type = $entity->userType;
        $model->status = $entity->status;

        return $model;
    }
}
