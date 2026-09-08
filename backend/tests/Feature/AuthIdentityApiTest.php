<?php

declare(strict_types=1);

use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

function attachMembership(User $user, Organization $organization, array $attributes = []): OrganizationMembership
{
    return app(TenantContext::class)->run(
        $organization->id,
        fn (): OrganizationMembership => OrganizationMembership::query()->create([
            'user_id' => $user->id,
            'status' => 'active',
            'access_expires_at' => now()->addMonth(),
            'joined_at' => now()->subMonth(),
            ...$attributes,
        ]),
    );
}

it('authenticates with the web guard, regenerates the session and avoids caching private api responses', function () {
    User::factory()->create([
        'email' => 'student@demo.local',
        'password' => Hash::make('correct-password'),
    ]);

    $this->withSession(['active_organization_id' => 999, 'sentinel' => 'kept']);
    $oldSessionId = session()->getId();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'student@demo.local',
        'password' => 'correct-password',
    ]);

    $response->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('data.email', 'student@demo.local')
        ->assertJsonPath('data.active_organization_id', null);

    expect(auth()->check())->toBeTrue()
        ->and(session()->getId())->not->toBe($oldSessionId)
        ->and(session()->has('active_organization_id'))->toBeFalse();
});

it('rejects invalid credentials without authenticating the request', function () {
    User::factory()->create([
        'email' => 'student@demo.local',
        'password' => Hash::make('correct-password'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'student@demo.local',
        'password' => 'wrong-password',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('lists only active memberships and selects only an allowed tenant', function () {
    $user = User::factory()->create();
    $activeAcademy = Organization::factory()->create(['name' => 'Ritmo Activo']);
    $expiredAcademy = Organization::factory()->create(['name' => 'Ritmo Expirado']);
    $inactiveAcademy = Organization::factory()->create(['name' => 'Ritmo Inactivo']);
    $disabledAcademy = Organization::factory()->create(['name' => 'Ritmo Cerrado', 'is_active' => false]);

    attachMembership($user, $activeAcademy);
    attachMembership($user, $expiredAcademy, ['access_expires_at' => now()->subDay()]);
    attachMembership($user, $inactiveAcademy, ['status' => 'inactive']);
    attachMembership($user, $disabledAcademy);

    $this->actingAs($user)
        ->getJson('/api/v1/tenants')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Ritmo Activo');

    $this->actingAs($user)
        ->putJson('/api/v1/tenant', ['organization_id' => $expiredAcademy->id])
        ->assertNotFound();

    $this->actingAs($user)
        ->putJson('/api/v1/tenant', ['organization_id' => $activeAcademy->id])
        ->assertOk()
        ->assertJsonPath('data.id', $activeAcademy->id)
        ->assertJsonPath('data.is_active', true);

    expect(session('active_organization_id'))->toBe($activeAcademy->id);
});

it('sets tenant context only for the current verified request and clears stale session tenants', function () {
    $user = User::factory()->create();
    $academy = Organization::factory()->create();
    attachMembership($user, $academy);

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $academy->id])
        ->getJson('/api/v1/tenant/context')
        ->assertOk()
        ->assertJsonPath('organization_id', $academy->id);

    expect(app(TenantContext::class)->id())->toBeNull();

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $academy->id + 100])
        ->getJson('/api/v1/tenant/context')
        ->assertStatus(409)
        ->assertJsonPath('message', 'Selecciona una academia activa.');

    expect(session()->has('active_organization_id'))->toBeFalse()
        ->and(app(TenantContext::class)->id())->toBeNull();
});

it('requires verified email for tenant-scoped private context', function () {
    $user = User::factory()->unverified()->create();
    $academy = Organization::factory()->create();
    attachMembership($user, $academy);

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $academy->id])
        ->getJson('/api/v1/tenant/context')
        ->assertForbidden();
});

it('updates the profile and clears verification when email changes', function () {
    $user = User::factory()->create(['email' => 'old@example.test']);

    $this->actingAs($user)
        ->patchJson('/api/v1/me', [
            'name' => 'Nombre Nuevo',
            'email' => 'new@example.test',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Nombre Nuevo')
        ->assertJsonPath('data.email_verified', false);

    expect($user->refresh()->email_verified_at)->toBeNull();
});

it('sends password reset responses without account enumeration and resets with a valid token', function () {
    Notification::fake();
    $user = User::factory()->create(['email' => 'reset@example.test']);

    $this->postJson('/api/v1/auth/forgot-password', ['email' => 'missing@example.test'])
        ->assertOk()
        ->assertJsonPath('message', 'Si el correo existe, enviaremos instrucciones para restablecer la contraseña.');

    $token = Password::broker()->createToken($user);

    $this->postJson('/api/v1/auth/reset-password', [
        'email' => 'reset@example.test',
        'token' => $token,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ])->assertOk()
        ->assertJsonPath('message', 'Contraseña actualizada.');

    expect(Hash::check('new-secure-password', $user->refresh()->password))->toBeTrue();
});

it('sends and accepts signed email verification links', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->postJson('/api/v1/auth/email/notification')
        ->assertOk()
        ->assertJsonPath('message', 'Enlace de verificación enviado.');

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(10),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)
        ->getJson($verificationUrl)
        ->assertOk()
        ->assertJsonPath('message', 'Correo verificado.');

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('logs out by invalidating the session and regenerating the csrf token', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['active_organization_id' => 1])
        ->postJson('/api/v1/auth/logout')
        ->assertNoContent();

    expect(auth()->check())->toBeFalse()
        ->and(session()->has('active_organization_id'))->toBeFalse();
});
