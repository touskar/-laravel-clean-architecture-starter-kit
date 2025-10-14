<?php

use App\Http\Controllers\Api\V1\Common\AuthController;
use App\Http\Middleware\JwtAuthMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| Following Clean Architecture, controllers are thin and delegate to use cases.
|
*/

// API v1 - Common - Authentication (Public endpoints)
Route::prefix('v1/common/auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/complete-registration', [AuthController::class, 'completeRegistration']);
});

// API v1 - Common - Authentication (Protected endpoints - requires JWT)
Route::prefix('v1/common/auth')->middleware(JwtAuthMiddleware::class)->group(function () {
    Route::get('/me', [AuthController::class, 'getCurrentUser']);
});
