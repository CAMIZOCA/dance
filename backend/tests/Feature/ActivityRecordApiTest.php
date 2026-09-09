<?php

declare(strict_types=1);

use App\Models\ActivityRecord;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function attachRecordMembership(User $user, Organization $organization, array $attributes = []): OrganizationMembership
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

it('stores and lists activity records for the active tenant', function () {
    $user = User::factory()->create();
    $academy = Organization::factory()->create();
    attachRecordMembership($user, $academy);

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $academy->id])
        ->postJson('/api/v1/records', [
            'type' => 'class',
            'label' => 'Laboratorio de piso',
            'metadata' => ['screen' => 'home'],
        ])
        ->assertCreated()
        ->assertJsonPath('data.type', 'class')
        ->assertJsonPath('data.label', 'Laboratorio de piso')
        ->assertJsonPath('data.metadata.screen', 'home');

    $this->assertDatabaseHas('activity_records', [
        'organization_id' => $academy->id,
        'user_id' => $user->id,
        'type' => 'class',
        'label' => 'Laboratorio de piso',
    ]);

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $academy->id])
        ->getJson('/api/v1/records')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.label', 'Laboratorio de piso');
});

it('keeps activity records isolated by tenant and user', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $activeAcademy = Organization::factory()->create(['name' => 'Activa']);
    $otherAcademy = Organization::factory()->create(['name' => 'Otra']);
    attachRecordMembership($user, $activeAcademy);
    attachRecordMembership($user, $otherAcademy);
    attachRecordMembership($otherUser, $activeAcademy);

    app(TenantContext::class)->run(
        $activeAcademy->id,
        fn (): ActivityRecord => ActivityRecord::query()->create([
            'user_id' => $user->id,
            'type' => 'agenda',
            'label' => 'Visible para Camila',
        ]),
    );

    app(TenantContext::class)->run(
        $activeAcademy->id,
        fn (): ActivityRecord => ActivityRecord::query()->create([
            'user_id' => $otherUser->id,
            'type' => 'agenda',
            'label' => 'De otra persona',
        ]),
    );

    app(TenantContext::class)->run(
        $otherAcademy->id,
        fn (): ActivityRecord => ActivityRecord::query()->create([
            'user_id' => $user->id,
            'type' => 'agenda',
            'label' => 'De otra academia',
        ]),
    );

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $activeAcademy->id])
        ->getJson('/api/v1/records')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.label', 'Visible para Camila');
});

it('requires a verified email and active tenant before writing records', function () {
    $user = User::factory()->unverified()->create();
    $academy = Organization::factory()->create();
    attachRecordMembership($user, $academy);

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $academy->id])
        ->postJson('/api/v1/records', ['type' => 'class', 'label' => 'Bloqueado'])
        ->assertForbidden();

    $verifiedUser = User::factory()->create();

    $this->actingAs($verifiedUser)
        ->postJson('/api/v1/records', ['type' => 'class', 'label' => 'Sin academia'])
        ->assertStatus(409)
        ->assertJsonPath('message', 'Selecciona una academia activa.');
});

it('validates the record payload', function () {
    $user = User::factory()->create();
    $academy = Organization::factory()->create();
    attachRecordMembership($user, $academy);

    $this->actingAs($user)
        ->withSession(['active_organization_id' => $academy->id])
        ->postJson('/api/v1/records', [
            'type' => 'unsupported',
            'label' => str_repeat('a', 161),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type', 'label']);
});
