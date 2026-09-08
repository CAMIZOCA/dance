<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\TenantOwnedBuilder;
use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\DanceGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DanceGroup extends Model
{
    /** @use HasFactory<DanceGroupFactory> */
    use BelongsToOrganization, HasFactory;

    /** @var class-string<TenantOwnedBuilder<static>> */
    protected static string $builder = TenantOwnedBuilder::class;

    protected $fillable = [
        'branch_id', 'dance_style_id', 'level_id', 'name', 'slug', 'description', 'is_private', 'is_active', 'settings',
    ];

    protected function casts(): array
    {
        return ['settings' => 'array', 'is_private' => 'boolean', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<DanceStyle, $this> */
    public function danceStyle(): BelongsTo
    {
        return $this->belongsTo(DanceStyle::class);
    }

    /** @return BelongsTo<Level, $this> */
    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    /** @return HasMany<GroupMembership, $this> */
    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }
}
