<?php

declare(strict_types=1);

use App\Models\DanceGroup;
use App\Models\GroupMembership;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\PlatformRole;
use App\Models\PlatformRoleAssignment;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;

uses(DatabaseMigrations::class);

it('rebuilds the same temporal and relational demo snapshot', function () {
    $snapshot = function (): array {
        return [
            'organizations' => Organization::query()->orderBy('id')->get(['id', 'slug', 'created_at', 'updated_at'])->toArray(),
            'users' => User::query()->orderBy('id')->get(['id', 'email', 'email_verified_at', 'created_at', 'updated_at'])->toArray(),
            'organization_memberships' => OrganizationMembership::withoutGlobalScope('organization')->orderBy('id')->get([
                'id', 'organization_id', 'user_id', 'status', 'access_expires_at', 'reactivation_requested_at', 'joined_at', 'created_at', 'updated_at',
            ])->toArray(),
            'groups' => DanceGroup::withoutGlobalScope('organization')->orderBy('id')->get(['id', 'organization_id', 'slug', 'created_at', 'updated_at'])->toArray(),
            'group_memberships' => GroupMembership::withoutGlobalScope('organization')->orderBy('id')->get([
                'id', 'organization_id', 'dance_group_id', 'user_id', 'responsibility', 'status', 'started_at', 'ended_at', 'created_at', 'updated_at',
            ])->toArray(),
            'roles' => Role::withoutGlobalScope('organization')->orderBy('id')->get(['id', 'organization_id', 'key', 'created_at', 'updated_at'])->toArray(),
            'role_assignments' => RoleAssignment::withoutGlobalScope('organization')->orderBy('id')->get(['id', 'organization_id', 'user_id', 'role_id', 'assigned_at', 'created_at', 'updated_at'])->toArray(),
            'platform_roles' => PlatformRole::query()->orderBy('id')->get(['id', 'key', 'created_at', 'updated_at'])->toArray(),
            'platform_assignments' => PlatformRoleAssignment::query()->orderBy('id')->get(['id', 'user_id', 'platform_role_id', 'assigned_at', 'created_at', 'updated_at'])->toArray(),
        ];
    };

    $this->seed(DatabaseSeeder::class);
    $firstBuild = $snapshot();

    $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
    $this->seed(DatabaseSeeder::class);

    expect($snapshot())->toBe($firstBuild)
        ->and($firstBuild['organizations'][0]['created_at'])->toBe('2026-09-07T12:00:00.000000Z');
});
