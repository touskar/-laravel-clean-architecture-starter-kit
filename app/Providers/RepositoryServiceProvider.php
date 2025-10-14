<?php

namespace App\Providers;

use App\Domain\Repositories\IOtpVerificationRepository;
use App\Domain\Repositories\ISessionRepository;
use App\Domain\Repositories\IUserRepository;
use App\Infrastructure\Repositories\OtpVerificationRepositoryImpl;
use App\Infrastructure\Repositories\SessionRepositoryImpl;
use App\Infrastructure\Repositories\UserRepositoryImpl;
use Illuminate\Support\ServiceProvider;

/**
 * RepositoryServiceProvider - Binds Domain Interfaces to Infrastructure Implementations
 *
 * This provider registers all repository implementations for dependency injection.
 * Following Clean Architecture principles, we bind interfaces to their concrete implementations.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Repository bindings for authentication and user management
        $this->app->bind(IUserRepository::class, UserRepositoryImpl::class);
        $this->app->bind(IOtpVerificationRepository::class, OtpVerificationRepositoryImpl::class);
        $this->app->bind(ISessionRepository::class, SessionRepositoryImpl::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
