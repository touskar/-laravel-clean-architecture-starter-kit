<?php

namespace App\Providers;

use App\Domain\Services\IRandomStringService;
use App\Domain\Services\IRateLimitService;
use App\Infrastructure\Services\RandomStringServiceImpl;
use App\Infrastructure\Services\RateLimitService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register RandomStringService
        $this->app->bind(IRandomStringService::class, RandomStringServiceImpl::class);

        // Register RateLimitService (cache store configured via CACHE_STORE env variable)
        $this->app->bind(IRateLimitService::class, RateLimitService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
