<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\TenantOwnedBuilder;
use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\GroupMembershipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMembership extends Model
{
    /** @use HasFactory<GroupMembershipFactory> */
    use BelongsToOrganization, HasFactory;

    /** @var class-string<TenantOwnedBuilder<static>> */
    protected static string $builder = TenantOwnedBuilder::class;

    protected $fillable = [
        'dance_group_id', 'user_id', 'responsibility', 'status', 'started_at', 'ended_at', 'assigned_by',
    ];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<DanceGroup, $this> */
    public function group(): BelongsTo
    {
        return $this->belongsTo(DanceGroup::class, 'dance_group_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
