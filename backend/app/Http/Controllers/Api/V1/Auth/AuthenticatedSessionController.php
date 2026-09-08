<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): UserResource
    {
        $credentials = $request->validated();
        $email = Str::lower((string) $credentials['email']);
        $rateLimitKey = Str::transliterate($email.'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => RateLimiter::availableIn($rateLimitKey),
                    'minutes' => ceil(RateLimiter::availableIn($rateLimitKey) / 60),
                ]),
            ]);
        }

        if (! Auth::guard('web')->attempt(['email' => $email, 'password' => $credentials['password']])) {
            RateLimiter::hit($rateLimitKey);

            throw ValidationException::withMessages([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ]);
        }

        RateLimiter::clear($rateLimitKey);
        $request->session()->regenerate();
        $request->session()->forget('active_organization_id');

        return new UserResource($request->user());
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(status: 204);
    }
}
