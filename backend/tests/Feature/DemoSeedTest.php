<?php

declare(strict_types=1);

use App\Models\DanceGroup;
use App\Models\GroupMembership;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Permission;
use App\Models\PlatformRole;
use App\Models\Role;
use App\Models\User;
use App\Tenancy\TenantContext;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('builds deterministic inspectable demo data and credentials', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Organization::query()->count())->toBe(2)
        ->and(User::query()->where('email', 'admin@demo.local')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'academy.admin@demo.local')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'teacher@demo.local')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'moderator@demo.local')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'student@demo.local')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'photographer@demo.local')->exists())->toBeTrue()
        ->and(Permission::query()->count())->toBeGreaterThanOrEqual(10)
        ->and(Role::withoutGlobalScope('organization')->where('key', 'academy_admin')->count())->toBe(2)
        ->and(PlatformRole::query()->where('key', 'platform_super_admin')->count())->toBe(1);

    $demoStudent = User::query()->where('email', 'student@demo.local')->firstOrFail();
    expect(Hash::check(DemoSeeder::PASSWORD, $demoStudent->password))->toBeTrue();

    $main = Organization::query()->where('slug', 'ritmo-demo-academy')->firstOrFail();
    app(TenantContext::class)->run($main->id, function () use ($demoStudent): void {
        $expiredUser = User::query()->where('email', 'student39@demo.local')->firstOrFail();
        $reactivationUser = User::query()->where('email', 'student40@demo.local')->firstOrFail();
        $expiredMembership = OrganizationMembership::query()->where('user_id', $expiredUser->id)->firstOrFail();
        $reactivationMembership = OrganizationMembership::query()->where('user_id', $reactivationUser->id)->firstOrFail();

        expect(DanceGroup::query()->count())->toBe(10)
            ->and(GroupMembership::query()->where('user_id', $demoStudent->id)->where('status', 'active')->count())->toBeGreaterThanOrEqual(2)
            ->and(GroupMembership::query()->where('user_id', $demoStudent->id)->where('status', 'ended')->count())->toBe(1)
            ->and($expiredMembership->status)->toBe('inactive')
            ->and($expiredMembership->access_expires_at->isPast())->toBeTrue()
            ->and($reactivationMembership->status)->toBe('reactivation_requested')
            ->and($reactivationMembership->access_expires_at->isPast())->toBeTrue()
            ->and($reactivationMembership->reactivation_requested_at)->not->toBeNull();
    });
});

it('refuses demo credentials outside the strict environment allowlist', function () {
    $originalEnvironment = $this->app->environment();
    $this->app->detectEnvironment(fn (): string => 'staging');

    try {
        expect(fn () => $this->seed(DatabaseSeeder::class))
            ->toThrow(RuntimeException::class, 'Demo data is allowed only in local and testing environments.');
        expect(User::query()->count())->toBe(0)
            ->and(Organization::query()->count())->toBe(0);
    } finally {
        $this->app->detectEnvironment(fn (): string => $originalEnvironment);
    }
});
