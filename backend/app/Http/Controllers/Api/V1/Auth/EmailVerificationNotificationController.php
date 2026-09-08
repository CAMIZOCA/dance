<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if ($request->user()?->hasVerifiedEmail()) {
            return response()->json(status: 204);
        }

        $request->user()?->sendEmailVerificationNotification();

        return response()->json(['message' => 'Enlace de verificación enviado.']);
    }
}
