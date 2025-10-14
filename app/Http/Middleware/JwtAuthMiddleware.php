<?php

namespace App\Http\Middleware;

use App\Domain\Repositories\ISessionRepository;
use App\Domain\Services\IJwtService;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

/**
 * JwtAuthMiddleware - Validates JWT tokens
 */
class JwtAuthMiddleware
{
    public function __construct(
        private readonly ISessionRepository $sessionRepository,
        private readonly IJwtService $jwtService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'code' => 'UNAUTHORIZED',
                'success' => false,
                'message' => 'Token manquant',
                'data' => null,
            ], 401);
        }

        try {
            JWTAuth::setToken($token);
            $payload = JWTAuth::getPayload();
            $userId = $payload->get('sub');

            // Add user ID to request for downstream use
            $request->attributes->set('user_id', $userId);

            // Update session last_ip and last_access_at
            $this->updateSessionActivity($userId, $token, $request);
        } catch (JWTException $e) {
            return response()->json([
                'code' => 'UNAUTHORIZED',
                'success' => false,
                'message' => 'Token invalide ou expiré',
                'data' => null,
            ], 401);
        }

        return $next($request);
    }

    /**
     * Update session activity (last_ip, last_access_at, updated_at)
     */
    private function updateSessionActivity(string $userId, string $token, Request $request): void
    {
        try {
            // Hash the token to find the session
            $hashedToken = $this->jwtService->hashToken($token);

            // Find the session
            $session = $this->sessionRepository->findByUserIdAndToken($userId, $hashedToken);

            if ($session !== null) {
                // Get client IP address
                $ipAddress = $request->ip();

                // Update session activity
                // Note: ipAddress maps to last_ip in database
                $session->ipAddress = $ipAddress;
                $session->lastUsedAt = new \DateTimeImmutable();

                // Save the session (updated_at will be handled by model timestamps)
                $this->sessionRepository->save($session);
            }
        } catch (\Exception $e) {
            // Log error but don't break the request
            \Log::error('Failed to update session activity', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
