<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): mixed
    {
        // Handle API requests with JSON responses
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Handle API exceptions with consistent JSON format
     */
    private function handleApiException($request, Throwable $e): JsonResponse
    {
        $statusCode = 500;
        $message = 'Une erreur interne est survenue';

        // Determine status code and message based on exception type
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: $message;
        } elseif ($e instanceof ValidationException) {
            $statusCode = 422;
            $message = 'Erreur de validation';

            return response()->json([
                'code' => 'ERROR',
                'success' => false,
                'message' => $message,
                'errors' => $e->errors(),
                'data' => null,
            ], $statusCode);
        }

        // Log the error
        Log::error('API Exception', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
        ]);

        // Build response
        $response = [
            'code' => 'ERROR',
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : $message,
            'data' => null,
        ];

        // Add debug information only in debug mode
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(10)->map(function ($trace) {
                    return [
                        'file' => $trace['file'] ?? 'unknown',
                        'line' => $trace['line'] ?? 0,
                        'function' => $trace['function'] ?? 'unknown',
                    ];
                })->toArray(),
            ];
        }

        return response()->json($response, $statusCode);
    }
}
