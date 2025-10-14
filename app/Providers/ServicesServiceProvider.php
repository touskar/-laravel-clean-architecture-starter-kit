<?php

namespace App\Providers;

use App\Domain\Services\IAuthenticationService;
use App\Domain\Services\IJwtService;
use App\Domain\Services\IOtpService;
use App\Infrastructure\Services\AuthenticationServiceImpl;
use App\Infrastructure\Services\JwtServiceImpl;
use App\Infrastructure\Services\OtpServiceImpl;
use Illuminate\Support\ServiceProvider;

/**
 * ServicesServiceProvider - Binds Domain Service Interfaces to Infrastructure Implementations
 *
 * This provider registers all domain services for dependency injection.
 * Domain services contain business logic that doesn't naturally fit within entities.
 */
class ServicesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Service bindings for authentication and OTP functionality
        $this->app->bind(IOtpService::class, OtpServiceImpl::class);
        $this->app->bind(IJwtService::class, JwtServiceImpl::class);
        $this->app->bind(IAuthenticationService::class, AuthenticationServiceImpl::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
