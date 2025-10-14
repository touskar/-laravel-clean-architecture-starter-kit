<?php

namespace App\Domain\Services;

/**
 * Rate Limit Service Interface
 * Provides rate limiting capabilities to prevent abuse and protect against DoS attacks
 * Typically implemented using Redis or other distributed cache for scalability
 */
interface IRateLimitService
{
    /**
     * Check if request is allowed under rate limit
     *
     * @param  string  $key  Rate limit key (e.g., "otp:send:+212612345678")
     * @param  int  $maxRequests  Maximum number of requests allowed within the time window
     * @param  int  $windowSeconds  Time window in seconds for rate limiting
     * @return bool true if request is allowed, false if rate limit exceeded
     */
    public function isAllowed(string $key, int $maxRequests, int $windowSeconds): bool;

    /**
     * Get remaining requests for a key within current time window
     *
     * @param  string  $key  Rate limit key
     * @param  int  $maxRequests  Maximum number of requests allowed
     * @return int Number of remaining requests (0 if limit exceeded)
     */
    public function getRemainingRequests(string $key, int $maxRequests): int;

    /**
     * Get time until rate limit resets for a key
     *
     * @param  string  $key  Rate limit key
     * @return int Seconds until reset, or 0 if no active limit
     */
    public function getTimeUntilReset(string $key): int;

    /**
     * Reset rate limit for a specific key
     * Useful for testing or admin actions to clear rate limits
     *
     * @param  string  $key  Rate limit key to reset
     */
    public function reset(string $key): void;
}
