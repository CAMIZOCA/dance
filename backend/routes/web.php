<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Api\V1\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Api\V1\Auth\NewPasswordController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\V1\Auth\VerifyEmailController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Middleware\EnsureActiveTenant;
use App\Http\Middleware\NoStoreApiResponses;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api/v1')
    ->middleware([NoStoreApiResponses::class])
    ->group(function (): void {
        Route::get('csrf-token', fn () => response()->json(['csrf_token' => csrf_token()]));

        Route::post('auth/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:login');
        Route::post('auth/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware('throttle:password-reset');
        Route::post('auth/reset-password', [NewPasswordController::class, 'store'])
            ->middleware('throttle:password-reset');

        Route::middleware('auth')->group(function (): void {
            Route::post('auth/logout', [AuthenticatedSessionController::class, 'destroy']);
            Route::post('auth/email/notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:verification');
            Route::get('auth/email/verify/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:verification'])
                ->name('verification.verify');

            Route::get('me', [MeController::class, 'show']);
            Route::patch('me', [MeController::class, 'update']);
            Route::get('tenants', [TenantController::class, 'index']);
            Route::put('tenant', [TenantController::class, 'update']);

            Route::get('tenant/context', fn () => response()->json(['organization_id' => app(TenantContext::class)->requireId()]))
                ->middleware(['verified', EnsureActiveTenant::class]);
        });
    });
