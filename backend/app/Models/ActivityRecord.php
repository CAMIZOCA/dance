<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\TenantOwnedBuilder;
use App\Models\Concerns\BelongsToOrganization;
use Database\Factories\ActivityRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityRecord extends Model
{
    /** @use HasFactory<ActivityRecordFactory> */
    use BelongsToOrganization, HasFactory;

    /** @var class-string<TenantOwnedBuilder<static>> */
    protected static string $builder = TenantOwnedBuilder::class;

    protected $fillable = [
        'user_id',
        'type',
        'label',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
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
