<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationMembership extends Model
{
    use BelongsToOrganization;

    protected $table = 'organization_user';

    protected $fillable = [
        'user_id', 'status', 'access_expires_at', 'reactivation_requested_at', 'reactivated_at', 'joined_at', 'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'access_expires_at' => 'datetime',
            'reactivation_requested_at' => 'datetime',
            'reactivated_at' => 'datetime',
            'joined_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
