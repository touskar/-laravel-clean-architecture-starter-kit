<?php

namespace App\Infrastructure\Services;

use App\Domain\Services\IRateLimitService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Rate limiting service using Laravel Cache abstraction
 *
 * Cache store is configured via CACHE_STORE environment variable:
 * - file: File-based cache (default, no setup required)
 * - redis: Redis cache (high performance, requires Redis server)
 * - database: Database cache (shared across servers)
 * - memcached: Memcached (distributed caching)
 * - array: In-memory cache (testing only)
 *
 * This service is cache-agnostic - it uses Laravel's Cache facade,
 * so switching cache stores is purely a configuration change.
 *
 * Implements sliding window rate limiting to prevent abuse.
 */
class RateLimitService implements IRateLimitService
{
    /**
     * Check if request is allowed under rate limit
     *
     * @param  string  $key  Rate limit key (e.g., "otp:send:+212612345678")
     * @param  int  $maxRequests  Maximum number of requests allowed
     * @param  int  $windowSeconds  Time window in seconds
     * @return bool true if request is allowed, false if rate limit exceeded
     */
    public function isAllowed(string $key, int $maxRequests, int $windowSeconds): bool
    {
        try {
            $cacheKey = "ratelimit:{$key}";

            // Get current count (ensure it's an integer)
            $currentCount = (int) Cache::get($cacheKey, 0);

            // Check if limit exceeded
            if ($currentCount >= $maxRequests) {
                Log::warning("Rate limit exceeded for key: {$key}");

                return false;
            }

            // Increment counter
            $newCount = $currentCount + 1;

            // Store with expiration
            if ($currentCount === 0) {
                // First request - set expiration
                Cache::put($cacheKey, $newCount, now()->addSeconds($windowSeconds));
            } else {
                // Subsequent requests - preserve existing expiration
                $ttl = (int) Cache::get("{$cacheKey}:ttl", $windowSeconds);
                Cache::put($cacheKey, $newCount, now()->addSeconds($ttl));
            }

            // Store TTL reference
            if ($currentCount === 0) {
                Cache::put("{$cacheKey}:ttl", $windowSeconds, now()->addSeconds($windowSeconds));
            }

            Log::debug("Rate limit check for key: {$key} - {$newCount}/{$maxRequests} requests");

            return true;

        } catch (\Exception $e) {
            Log::error("Error checking rate limit for key: {$key}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Fail open - allow request if cache is unavailable
            return true;
        }
    }

    /**
     * Get remaining requests for a key
     *
     * @param  string  $key  Rate limit key
     * @param  int  $maxRequests  Maximum number of requests allowed
     * @return int Number of remaining requests
     */
    public function getRemainingRequests(string $key, int $maxRequests): int
    {
        try {
            $cacheKey = "ratelimit:{$key}";
            $currentCount = (int) Cache::get($cacheKey, 0);

            return max(0, $maxRequests - $currentCount);
        } catch (\Exception $e) {
            Log::error("Error getting remaining requests for key: {$key}", [
                'error' => $e->getMessage(),
            ]);

            return $maxRequests;
        }
    }

    /**
     * Get time until rate limit resets (in seconds)
     *
     * @param  string  $key  Rate limit key
     * @return int Seconds until reset, or 0 if no limit active
     */
    public function getTimeUntilReset(string $key): int
    {
        try {
            $cacheKey = "ratelimit:{$key}:ttl";
            $ttl = (int) Cache::get($cacheKey, 0);

            return max(0, $ttl);
        } catch (\Exception $e) {
            Log::error("Error getting TTL for key: {$key}", [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Reset rate limit for a key (useful for testing or admin actions)
     *
     * @param  string  $key  Rate limit key
     */
    public function reset(string $key): void
    {
        try {
            $cacheKey = "ratelimit:{$key}";
            Cache::forget($cacheKey);
            Cache::forget("{$cacheKey}:ttl");
            Log::info("Rate limit reset for key: {$key}");
        } catch (\Exception $e) {
            Log::error("Error resetting rate limit for key: {$key}", [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
