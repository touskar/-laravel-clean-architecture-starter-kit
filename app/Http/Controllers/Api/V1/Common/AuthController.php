<?php

namespace App\Http\Controllers\Api\V1\Common;

use App\Application\DTOs\Requests\CompleteRegistrationRequest;
use App\Application\DTOs\Requests\SendOtpRequest;
use App\Application\DTOs\Requests\VerifyOtpRequest;
use App\Application\Presenters\Auth\CompleteRegistrationPresenter;
use App\Application\Presenters\Auth\GetCurrentUserPresenter;
use App\Application\Presenters\Auth\SendOtpPresenter;
use App\Application\Presenters\Auth\VerifyOtpPresenter;
use App\Application\UseCases\Auth\CompleteRegistrationUseCase;
use App\Application\UseCases\Auth\GetCurrentUserUseCase;
use App\Application\UseCases\Auth\SendOtpUseCase;
use App\Application\UseCases\Auth\VerifyOtpUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * AuthController - Thin controller that delegates to UseCases
 */
class AuthController extends Controller
{
    /**
     * POST /api/v1/common/auth/send-otp
     */
    public function sendOtp(Request $request): JsonResponse
    {
        try {
            \DB::beginTransaction();

            // DEBUG - Check what sendOtp sees
            \Log::info('SendOtp Request Debug', [
                'input' => $request->input(),
                'all' => $request->all(),
                'content_length' => strlen($request->getContent())
            ]);

            // Parse request to DTO
            $dto = SendOtpRequest::fromArray($request->all());

            // Create presenter
            $presenter = app(SendOtpPresenter::class);

            // Execute use case
            $useCase = app(SendOtpUseCase::class, ['presenter' => $presenter]);
            $useCase->execute($dto);

            \DB::commit();

            // Return formatted response
            $statusCode = $presenter->hasError() ? 400 : 200;
            return response_json($presenter->getData(), $statusCode);
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('SendOtp Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response_json([
                'code' => 'ERROR',
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/common/auth/verify-otp
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        try {
            \DB::beginTransaction();

            // Add IP address and user agent from HTTP request metadata
            $data = array_merge($request->all(), [
                'ipAddress' => $request->ip(),
                'userAgent' => $request->userAgent()
            ]);

            // Parse request to DTO
            $dto = VerifyOtpRequest::fromArray($data);

            // Create presenter
            $presenter = app(VerifyOtpPresenter::class);

            // Execute use case
            $useCase = app(VerifyOtpUseCase::class, ['presenter' => $presenter]);
            $useCase->execute($dto);

            \DB::commit();

            // Return formatted response
            $statusCode = $presenter->hasError() ? 400 : 200;
            return response_json($presenter->getData(), $statusCode);
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('VerifyOtp Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response_json([
                'code' => 'ERROR',
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * POST /api/v1/common/auth/complete-registration
     */
    public function completeRegistration(Request $request): JsonResponse
    {
        try {
            \DB::beginTransaction();

            // Get raw content
            $rawContent = $request->getContent();

            // Parse JSON
            $data = json_decode($rawContent, true) ?? [];

            // DEBUG
            \Log::info('CompleteRegistration Request Debug', [
                'raw_content_length' => strlen($rawContent),
                'raw_content' => $rawContent,
                'json_decode_error' => json_last_error_msg(),
                'parsed_data' => $data
            ]);

            // Add IP address and user agent from HTTP request metadata
            $data['ipAddress'] = $request->ip();
            $data['userAgent'] = $request->userAgent();

            // Parse request to DTO
            $dto = CompleteRegistrationRequest::fromArray($data);

            // Create presenter
            $presenter = app(CompleteRegistrationPresenter::class);

            // Execute use case
            $useCase = app(CompleteRegistrationUseCase::class, ['presenter' => $presenter]);
            $useCase->execute($dto);

            \DB::commit();

            // Return formatted response
            $statusCode = $presenter->hasError() ? 400 : 201;
            return response_json($presenter->getData(), $statusCode);
        } catch (\Exception $e) {
            \DB::rollBack();

            \Log::error('CompleteRegistration Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response_json([
                'code' => 'ERROR',
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    /**
     * GET /api/v1/common/auth/me
     */
    public function getCurrentUser(Request $request): JsonResponse
    {
        try {
            // Create presenter
            $presenter = app(GetCurrentUserPresenter::class);

            // Execute use case
            $useCase = app(GetCurrentUserUseCase::class, ['presenter' => $presenter]);
            $useCase->execute();

            // Return formatted response
            $statusCode = $presenter->hasError() ? 401 : 200;
            return response_json($presenter->getData(), $statusCode);
        } catch (\Exception $e) {
            \Log::error('GetCurrentUser Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response_json([
                'code' => 'ERROR',
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }
}
