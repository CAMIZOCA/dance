<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Builders\TenantOwnedBuilder;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LogicException;

trait BelongsToOrganization
{
    /** @var class-string<TenantOwnedBuilder<*>> */
    protected static string $builder = TenantOwnedBuilder::class;

    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $builder): void {
            $organizationId = app(TenantContext::class)->id();

            if ($organizationId === null) {
                $builder->whereRaw('1 = 0');

                return;
            }

            $builder->where($builder->qualifyColumn('organization_id'), $organizationId);
        });

        static::creating(function (Model $model): void {
            $organizationId = app(TenantContext::class)->requireId();
            $modelOrganizationId = $model->getAttribute('organization_id');

            if ($modelOrganizationId === null) {
                $model->setAttribute('organization_id', $organizationId);

                return;
            }

            if ((int) $modelOrganizationId !== $organizationId) {
                throw new LogicException('The model organization does not match the active tenant.');
            }
        });

        static::updating(function (Model $model): void {
            $organizationId = app(TenantContext::class)->requireId();

            if ($model->isDirty('organization_id') || (int) $model->getAttribute('organization_id') !== $organizationId) {
                throw new LogicException('Tenant ownership is immutable and must match the active tenant.');
            }
        });
    }
}
