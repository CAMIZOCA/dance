<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Builders\TenantOwnedBuilder;
use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class RoleAssignmentScope extends Model
{
    use BelongsToOrganization;

    /** @var class-string<TenantOwnedBuilder<static>> */
    protected static string $builder = TenantOwnedBuilder::class;

    protected $fillable = ['role_assignment_id', 'scope_type', 'scope_id'];

    protected static function booted(): void
    {
        static::saving(function (self $scope): void {
            if (trim((string) $scope->scope_type) === '' || (int) $scope->scope_id < 1) {
                throw new InvalidArgumentException('A role assignment scope requires a type and a positive identifier.');
            }
        });
    }

    /** @return BelongsTo<RoleAssignment, $this> */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(RoleAssignment::class, 'role_assignment_id');
    }

    /** @return BelongsTo<Organization, $this> */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
