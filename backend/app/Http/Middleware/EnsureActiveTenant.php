<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\OrganizationMembership;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveTenant
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->tenantContext->forget();

        $user = $request->user();
        $organizationId = $request->session()->get('active_organization_id');

        if ($user === null || ! is_int($organizationId)) {
            return response()->json(['message' => 'Selecciona una academia activa.'], 409);
        }

        $hasActiveMembership = OrganizationMembership::withoutGlobalScope('organization')
            ->where('user_id', $user->id)
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->where(function ($query): void {
                $query->whereNull('access_expires_at')
                    ->orWhere('access_expires_at', '>', now());
            })
            ->whereHas('organization', fn ($query) => $query->where('is_active', true))
            ->exists();

        if (! $hasActiveMembership) {
            $request->session()->forget('active_organization_id');

            return response()->json(['message' => 'Selecciona una academia activa.'], 409);
        }

        try {
            return $this->tenantContext->run($organizationId, fn (): Response => $next($request));
        } finally {
            $this->tenantContext->forget();
        }
    }
}
