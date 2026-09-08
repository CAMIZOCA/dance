<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\TenantOwnedBuilder;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RoleAssignment extends Model
{
    use BelongsToOrganization;

    /** @var class-string<TenantOwnedBuilder<static>> */
    protected static string $builder = TenantOwnedBuilder::class;

    protected $fillable = ['user_id', 'role_id', 'assigned_at', 'revoked_at'];

    protected function casts(): array
    {
        return ['assigned_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return HasOne<RoleAssignmentScope, $this> */
    public function scope(): HasOne
    {
        return $this->hasOne(RoleAssignmentScope::class);
    }
}
