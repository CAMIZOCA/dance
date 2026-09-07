<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['key', 'name', 'description', 'scope'];

    /** @return BelongsToMany<Role, $this> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** @return BelongsToMany<PlatformRole, $this> */
    public function platformRoles(): BelongsToMany
    {
        return $this->belongsToMany(PlatformRole::class);
    }
}
