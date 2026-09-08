<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\OrganizationMembership;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use LogicException;

class TenantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $membership = $this->resource;

        if (! $membership instanceof OrganizationMembership) {
            throw new LogicException('TenantResource expects an organization membership model.');
        }

        $organization = $membership->organization;

        return [
            'id' => $organization->id,
            'name' => $organization->name,
            'slug' => $organization->slug,
            'primary_color' => $organization->primary_color,
            'secondary_color' => $organization->secondary_color,
            'membership' => [
                'status' => $membership->status,
                'access_expires_at' => $this->serializeDateValue($membership->access_expires_at),
                'joined_at' => $this->serializeDateValue($membership->joined_at),
            ],
            'is_active' => $request->session()->get('active_organization_id') === $organization->id,
        ];
    }

    private function serializeDateValue(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_string($value)) {
            return $value;
        }

        return null;
    }
}
