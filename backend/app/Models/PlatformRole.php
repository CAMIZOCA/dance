<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class PlatformRole extends Model
{
    protected $fillable = ['key', 'name', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    /** @return BelongsToMany<Permission, $this> */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withPivot('permission_scope');
    }

    /** @return HasMany<PlatformRoleAssignment, $this> */
    public function assignments(): HasMany
    {
        return $this->hasMany(PlatformRoleAssignment::class);
    }

    public function grantPermission(Permission $permission): void
    {
        if ($permission->scope !== 'platform') {
            throw new InvalidArgumentException('Tenant permissions cannot be assigned to platform roles.');
        }

        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }
}
