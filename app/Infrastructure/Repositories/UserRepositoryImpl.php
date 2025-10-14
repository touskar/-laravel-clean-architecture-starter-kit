<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Entities\User;
use App\Domain\Repositories\IUserRepository;
use App\Infrastructure\Mappers\UserMapper;
use App\Infrastructure\Models\User as UserModel;

/**
 * UserRepositoryImpl - Implementation with "Impl" suffix
 * Bridges Domain and Infrastructure
 */
class UserRepositoryImpl implements IUserRepository
{
    public function __construct(
        private readonly UserMapper $mapper
    ) {
    }

    public function save(User $user): User
    {
        $model = $this->mapper->toModel($user);
        $model->save();

        return $this->mapper->toDomain($model);
    }

    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);
        return $this->mapper->toDomain($model);
    }

    public function findByPhoneNumber(string $phoneNumber): ?User
    {
        $model = UserModel::where('phone_number', $phoneNumber)->first();
        return $this->mapper->toDomain($model);
    }

    public function findByPhoneNumberAndUserType(string $phoneNumber, string $userType): ?User
    {
        $model = UserModel::where('phone_number', $phoneNumber)
            ->where('user_type', $userType)
            ->first();
        return $this->mapper->toDomain($model);
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserModel::where('email', $email)->first();
        return $this->mapper->toDomain($model);
    }

    public function findByUsername(string $username): ?User
    {
        $model = UserModel::where('username', $username)->first();
        return $this->mapper->toDomain($model);
    }

    public function existsByPhoneNumber(string $phoneNumber): bool
    {
        return UserModel::where('phone_number', $phoneNumber)->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return UserModel::where('email', $email)->exists();
    }

    public function existsByUsername(string $username): bool
    {
        return UserModel::where('username', $username)->exists();
    }

    public function findByIdWithRelations(string $id): ?User
    {
        $model = UserModel::with([
            'country',
            'contentCreator.mainCountry',
            'contentCreator.countries',
            'contentCreator.categories',
            'contentCreator.socialNetworks.socialNetwork',
            'contentCreator.members',
            'advertiserCompany.mainCountry',
            'advertiserCompany.countries',
            'advertiserCompany.categories',
            'advertiserCompany.members'
        ])->find($id);

        return $this->mapper->toDomain($model);
    }
}
