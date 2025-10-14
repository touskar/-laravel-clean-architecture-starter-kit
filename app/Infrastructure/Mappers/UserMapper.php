<?php

namespace App\Infrastructure\Mappers;

use App\Domain\Entities\User as UserEntity;
use App\Infrastructure\Models\User as UserModel;

/**
 * UserMapper - Eloquent Model ↔ Domain Entity
 */
class UserMapper
{
    public function __construct(
        private readonly CountryMapper $countryMapper,
        private readonly ContentCreatorMapper $contentCreatorMapper,
        private readonly AdvertiserCompanyMapper $advertiserCompanyMapper
    ) {
    }

    public function toDomain(?UserModel $model): ?UserEntity
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
            phoneNumber: $model->phone_number,
            password: $model->password,
            address: $model->address,
            userType: $model->user_type,
            status: $model->status,
            country: $this->countryMapper->toDomain($model->country),
            contentCreator: $this->contentCreatorMapper->toDomain($model->contentCreator),
            advertiserCompany: $this->advertiserCompanyMapper->toDomain($model->advertiserCompany),
            createdAt: $model->created_at ? \DateTimeImmutable::createFromMutable($model->created_at) : null,
            updatedAt: $model->updated_at ? \DateTimeImmutable::createFromMutable($model->updated_at) : null
        );
    }

    public function toModel(UserEntity $entity): UserModel
    {
        $model = new UserModel();
        $model->id = $entity->id;
        $model->name = $entity->name;
        $model->first_name = $entity->firstName;
        $model->last_name = $entity->lastName;
        $model->email = $entity->email;
        $model->username = $entity->username;
        $model->phone_number = $entity->phoneNumber;
        $model->password = $entity->password;
        $model->address = $entity->address;
        $model->user_type = $entity->userType;
        $model->status = $entity->status;

        if ($entity->country && $entity->country->id) {
            $model->country_id = $entity->country->id;
        }

        return $model;
    }
}
