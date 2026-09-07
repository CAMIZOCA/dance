<?php

declare(strict_types=1);

use App\Models\Branch;
use App\Models\DanceGroup;
use App\Models\DanceStyle;
use App\Models\Level;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Permission;
use App\Models\PlatformRole;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\RoleAssignmentScope;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createTenantGroup(Organization $organization, string $suffix): DanceGroup
{
    return app(TenantContext::class)->run($organization->id, function () use ($organization, $suffix): DanceGroup {
        $branch = Branch::factory()->create(['organization_id' => $organization->id, 'slug' => "branch-{$suffix}"]);
        $style = DanceStyle::factory()->create(['organization_id' => $organization->id, 'slug' => "style-{$suffix}"]);
        $level = Level::factory()->create(['organization_id' => $organization->id, 'slug' => "level-{$suffix}"]);

        return DanceGroup::factory()->create([
            'organization_id' => $organization->id,
            'branch_id' => $branch->id,
            'dance_style_id' => $style->id,
            'level_id' => $level->id,
            'slug' => "group-{$suffix}",
        ]);
    });
}

it('fails closed and only exposes records from the active tenant', function () {
    $academyA = Organization::factory()->create(['slug' => 'academy-a']);
    $academyB = Organization::factory()->create(['slug' => 'academy-b']);
    $groupA = createTenantGroup($academyA, 'a');
    $groupB = createTenantGroup($academyB, 'b');

    expect(DanceGroup::query()->count())->toBe(0);

    app(TenantContext::class)->run($academyA->id, function () use ($groupA, $groupB): void {
        expect(DanceGroup::query()->pluck('id')->all())->toBe([$groupA->id])
            ->and(DanceGroup::query()->find($groupB->id))->toBeNull();
    });

    app(TenantContext::class)->run($academyB->id, function () use ($groupB): void {
        expect(DanceGroup::query()->pluck('id')->all())->toBe([$groupB->id]);
    });
});

it('rejects cross-tenant relationships at the database boundary', function () {
    $academyA = Organization::factory()->create(['slug' => 'academy-a']);
    $academyB = Organization::factory()->create(['slug' => 'academy-b']);
    $groupA = createTenantGroup($academyA, 'a');
    $groupB = createTenantGroup($academyB, 'b');

    app(TenantContext::class)->run($academyA->id, fn () => DanceGroup::query()->create([
        'organization_id' => $academyA->id,
        'branch_id' => $groupB->branch_id,
        'dance_style_id' => $groupA->dance_style_id,
        'level_id' => $groupA->level_id,
        'name' => 'Invalid cross tenant group',
        'slug' => 'invalid-cross-tenant',
    ]));
})->throws(QueryException::class);

it('cannot disguise a tenant role assignment as a platform assignment with a null organization', function () {
    $academy = Organization::factory()->create(['slug' => 'academy-a']);
    $user = User::factory()->create();

    [$role] = app(TenantContext::class)->run($academy->id, function () use ($academy, $user): array {
        OrganizationMembership::query()->create([
            'organization_id' => $academy->id,
            'user_id' => $user->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return [Role::query()->create([
            'organization_id' => $academy->id,
            'key' => 'teacher',
            'name' => 'Teacher',
        ])];
    });

    DB::table('role_assignments')->insert([
        'organization_id' => null,
        'user_id' => $user->id,
        'role_id' => $role->id,
        'assigned_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
})->throws(QueryException::class);

it('injects immutable tenant ownership and rejects explicit cross-tenant model writes', function () {
    $academyA = Organization::factory()->create(['slug' => 'academy-a']);
    $academyB = Organization::factory()->create(['slug' => 'academy-b']);

    app(TenantContext::class)->run($academyA->id, function () use ($academyA, $academyB): void {
        $branch = Branch::query()->create([
            'organization_id' => $academyB->id,
            'name' => 'Safe branch',
            'slug' => 'safe-branch',
        ]);
        $role = Role::query()->create([
            'organization_id' => $academyB->id,
            'key' => 'teacher',
            'name' => 'Teacher',
        ]);

        expect($branch->organization_id)->toBe($academyA->id)
            ->and($role->organization_id)->toBe($academyA->id);

        expect(fn () => Branch::factory()->create([
            'organization_id' => $academyB->id,
            'slug' => 'forced-cross-tenant',
        ]))->toThrow(LogicException::class, 'The model organization does not match the active tenant.');

        $branch->forceFill(['organization_id' => $academyB->id]);
        expect(fn () => $branch->save())
            ->toThrow(LogicException::class, 'Tenant ownership is immutable and must match the active tenant.');
    });
});

it('rejects tenant ownership changes through mass updates', function () {
    $academyA = Organization::factory()->create(['slug' => 'academy-a']);
    $academyB = Organization::factory()->create(['slug' => 'academy-b']);
    $branch = app(TenantContext::class)->run($academyA->id, fn () => Branch::query()->create([
        'name' => 'Tenant A Branch',
        'slug' => 'tenant-a-branch',
    ]));

    app(TenantContext::class)->run($academyA->id, fn () => Branch::query()
        ->whereKey($branch->id)
        ->update(['organization_id' => $academyB->id]));
})->throws(LogicException::class, 'Tenant ownership cannot be changed by a bulk operation.');

it('rejects tenant ownership changes through numeric and upsert operations', function () {
    $academyA = Organization::factory()->create(['slug' => 'academy-a']);
    $academyB = Organization::factory()->create(['slug' => 'academy-b']);
    $branch = app(TenantContext::class)->run($academyA->id, fn () => Branch::query()->create([
        'name' => 'Tenant A Branch',
        'slug' => 'tenant-a-branch',
    ]));

    app(TenantContext::class)->run($academyA->id, function () use ($academyB, $branch): void {
        expect(fn () => Branch::query()->whereKey($branch->id)
            ->increment('id', 0, ['organization_id' => $academyB->id]))
            ->toThrow(LogicException::class, 'Tenant ownership cannot be changed by a bulk operation.');

        expect(fn () => Branch::query()->whereKey($branch->id)
            ->decrementEach(['id' => 0], ['organization_id' => $academyB->id]))
            ->toThrow(LogicException::class, 'Tenant ownership cannot be changed by a bulk operation.');

        expect(fn () => Branch::query()->upsert([
            'organization_id' => $academyB->id,
            'name' => 'Cross tenant upsert',
            'slug' => 'cross-tenant-upsert',
        ], ['organization_id', 'slug'], ['name']))
            ->toThrow(LogicException::class, 'Upsert rows must belong to the active tenant.');

        expect(fn () => Branch::query()->upsert([
            'name' => 'Unsafe unique key',
            'slug' => 'unsafe-unique-key',
        ], ['slug'], ['name']))
            ->toThrow(LogicException::class, 'Tenant upserts require organization_id in the unique key.');
    });
});

it('blocks platform-only permissions from tenant roles in the model and database', function () {
    $academy = Organization::factory()->create(['slug' => 'academy-a']);
    $platformPermission = Permission::query()->create([
        'key' => 'platform.manage',
        'name' => 'Platform Manage',
        'scope' => 'platform',
    ]);

    $role = app(TenantContext::class)->run($academy->id, fn () => Role::query()->create([
        'key' => 'academy_admin',
        'name' => 'Academy Admin',
    ]));

    expect(fn () => $role->grantPermission($platformPermission))
        ->toThrow(InvalidArgumentException::class, 'Platform-only permissions cannot be assigned to tenant roles.');

    DB::table('permission_role')->insert([
        'permission_id' => $platformPermission->id,
        'role_id' => $role->id,
        'permission_scope' => 'tenant',
    ]);
})->throws(QueryException::class);

it('blocks tenant permissions from platform roles in the model and database', function () {
    $tenantPermission = Permission::query()->create([
        'key' => 'groups.manage',
        'name' => 'Manage Groups',
        'scope' => 'tenant',
    ]);
    $platformRole = PlatformRole::query()->create([
        'key' => 'platform_super_admin',
        'name' => 'Platform Super Admin',
    ]);

    expect(fn () => $platformRole->grantPermission($tenantPermission))
        ->toThrow(InvalidArgumentException::class, 'Tenant permissions cannot be assigned to platform roles.');

    DB::table('permission_platform_role')->insert([
        'permission_id' => $tenantPermission->id,
        'platform_role_id' => $platformRole->id,
        'permission_scope' => 'platform',
    ]);
})->throws(QueryException::class);

it('stores role assignment scope as a complete tenant-owned pair', function () {
    $academy = Organization::factory()->create(['slug' => 'academy-a']);
    $user = User::factory()->create();

    app(TenantContext::class)->run($academy->id, function () use ($academy, $user): void {
        OrganizationMembership::query()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
        $role = Role::query()->create(['key' => 'moderator', 'name' => 'Moderator']);
        $assignment = RoleAssignment::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'assigned_at' => now(),
        ]);
        $scope = RoleAssignmentScope::query()->create([
            'role_assignment_id' => $assignment->id,
            'scope_type' => 'dance_group',
            'scope_id' => 10,
        ]);

        expect($scope->organization_id)->toBe($academy->id)
            ->and($assignment->scope()->is($scope))->toBeTrue();

        expect(fn () => RoleAssignmentScope::query()->create([
            'role_assignment_id' => $assignment->id,
            'scope_type' => '',
            'scope_id' => 0,
        ]))->toThrow(InvalidArgumentException::class);

        expect(fn () => DB::table('role_assignment_scopes')->insert([
            'organization_id' => $academy->id,
            'role_assignment_id' => $assignment->id + 1000,
            'scope_type' => null,
            'scope_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toThrow(QueryException::class);
    });
});
