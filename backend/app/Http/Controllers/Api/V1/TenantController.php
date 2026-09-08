<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SelectTenantRequest;
use App\Http\Resources\Api\V1\TenantResource;
use App\Models\OrganizationMembership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TenantController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return TenantResource::collection($this->activeMemberships((int) $request->user()->id)->get());
    }

    public function update(SelectTenantRequest $request): TenantResource
    {
        $membership = $this->activeMemberships((int) $request->user()->id)
            ->where('organization_id', $request->integer('organization_id'))
            ->first();

        if ($membership === null) {
            abort(404, 'Academia no disponible.');
        }

        $request->session()->put('active_organization_id', (int) $membership->organization_id);

        return new TenantResource($membership);
    }

    /** @return Builder<OrganizationMembership> */
    private function activeMemberships(int $userId): Builder
    {
        return OrganizationMembership::withoutGlobalScope('organization')
            ->with('organization')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->whereNull('ended_at')
            ->where(function ($query): void {
                $query->whereNull('access_expires_at')
                    ->orWhere('access_expires_at', '>', now());
            })
            ->whereHas('organization', fn ($query) => $query->where('is_active', true))
            ->orderBy('organization_id');
    }
}
