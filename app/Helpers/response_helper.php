<?php

if (!function_exists('response_json')) {
    /**
     * Create a JSON response with automatic logging
     *
     * @param mixed $data The data to return in the response
     * @param int $status HTTP status code
     * @param array $headers Additional headers
     * @param int $options JSON encoding options
     * @return \Illuminate\Http\JsonResponse
     */
    function response_json(
        mixed $data = [],
        int $status = 200,
        array $headers = [],
        int $options = 0
    ): \Illuminate\Http\JsonResponse {
        // Log the response data
        \Log::info('API Response', [
            'status' => $status,
            'data' => $data,
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Return the JSON response
        return response()->json($data, $status, $headers, $options);
    }
}

if (!function_exists('current_user')) {
    /**
     * Get the currently authenticated user from request bearer token
     *
     * @return \App\Domain\Entities\User
     * @throws \RuntimeException if token is invalid or user not found
     */
    function current_user(): \App\Domain\Entities\User
    {
        $authService = app(\App\Domain\Services\IAuthenticationService::class);
        return $authService->getCurrentUserFromRequest();
    }
}
