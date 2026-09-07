<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\DanceGroup;
use App\Models\DanceStyle;
use App\Models\GroupMembership;
use App\Models\Level;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Permission;
use App\Models\PlatformRole;
use App\Models\PlatformRoleAssignment;
use App\Models\Role;
use App\Models\RoleAssignment;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public const PASSWORD = 'DanceDemo2026!';

    public const BASE_DATE = '2026-09-07 12:00:00';

    private ?string $demoPasswordHash = null;

    /** @var array<string, array<int, string>> */
    private array $rolePermissions = [
        'academy_admin' => ['organization.manage', 'branches.manage', 'styles.manage', 'levels.manage', 'groups.view', 'groups.manage', 'groups.members.manage', 'classes.view', 'classes.manage', 'media.upload', 'media.review', 'community.moderate'],
        'coordinator' => ['branches.manage', 'styles.manage', 'levels.manage', 'groups.view', 'groups.manage', 'groups.members.manage', 'classes.view', 'classes.manage', 'media.review', 'community.moderate'],
        'teacher' => ['groups.view', 'groups.members.manage', 'classes.view', 'classes.manage', 'media.upload', 'media.review'],
        'assistant_teacher' => ['groups.view', 'classes.view', 'classes.manage', 'media.upload'],
        'moderator' => ['groups.view', 'media.review', 'community.moderate'],
        'student' => ['groups.view', 'classes.view', 'media.upload'],
        'photographer' => ['groups.view', 'media.upload'],
        'videographer' => ['groups.view', 'media.upload'],
    ];

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo data is allowed only in local and testing environments.');
        }

        $previousTestNow = Carbon::getTestNow();
        Carbon::setTestNow($this->baseDate());

        try {
            $this->seedData();
        } finally {
            Carbon::setTestNow($previousTestNow);
        }
    }

    private function seedData(): void
    {
        $permissions = $this->seedPermissions();
        $platformRole = PlatformRole::query()->create([
            'key' => 'platform_super_admin',
            'name' => 'Platform Super Admin',
            'is_system' => true,
        ]);
        $platformRole->permissions()->sync($permissions->where('scope', 'platform')->pluck('id'));

        $platformAdmin = $this->user('Platform Demo Admin', 'admin@demo.local');
        PlatformRoleAssignment::query()->create([
            'user_id' => $platformAdmin->id,
            'platform_role_id' => $platformRole->id,
            'assigned_at' => $this->baseDate(),
        ]);

        $main = Organization::query()->create([
            'name' => 'Ritmo Demo Academy',
            'slug' => 'ritmo-demo-academy',
            'primary_color' => '#7C3AED',
            'secondary_color' => '#111827',
            'whatsapp_number' => '+573001234567',
            'contact_email' => 'hola@ritmodemo.local',
            'subdomain' => 'ritmo-demo',
            'settings' => ['default_access_months' => 3, 'locale' => 'es'],
        ]);

        $secondary = Organization::query()->create([
            'name' => 'Movimiento Norte Academy',
            'slug' => 'movimiento-norte-academy',
            'primary_color' => '#0F766E',
            'secondary_color' => '#0F172A',
            'contact_email' => 'hola@movimientonorte.local',
            'subdomain' => 'movimiento-norte',
            'settings' => ['default_access_months' => 3, 'locale' => 'es'],
        ]);

        $this->seedMainAcademy($main, $permissions);
        $this->seedSecondaryAcademy($secondary, $permissions);
    }

    /** @return Collection<int, Permission> */
    private function seedPermissions(): Collection
    {
        $definitions = [
            'platform.manage' => 'Manage the SaaS platform',
            'organization.manage' => 'Manage academy settings',
            'branches.manage' => 'Manage academy branches',
            'styles.manage' => 'Manage dance styles',
            'levels.manage' => 'Manage academy levels',
            'groups.view' => 'View authorized private groups',
            'groups.manage' => 'Manage groups',
            'groups.members.manage' => 'Manage group memberships',
            'classes.view' => 'View authorized classes',
            'classes.manage' => 'Manage classes',
            'media.upload' => 'Upload media in authorized scope',
            'media.review' => 'Review media in authorized scope',
            'community.moderate' => 'Moderate community content in authorized scope',
        ];

        foreach ($definitions as $key => $description) {
            Permission::query()->create([
                'key' => $key,
                'name' => str($key)->replace('.', ' ')->title(),
                'description' => $description,
                'scope' => $key === 'platform.manage' ? 'platform' : 'tenant',
            ]);
        }

        return Permission::query()->get();
    }

    /** @param Collection<int, Permission> $permissions */
    private function seedMainAcademy(Organization $organization, Collection $permissions): void
    {
        app(TenantContext::class)->run($organization->id, function () use ($organization, $permissions): void {
            $roles = $this->seedRoles($organization, $permissions);
            $branches = collect(['Centro', 'Norte'])->mapWithKeys(fn (string $name) => [
                Str::slug($name) => Branch::query()->create([
                    'organization_id' => $organization->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'address' => $name === 'Centro' ? 'Carrera 7 # 22-18' : 'Avenida Norte # 108-42',
                ]),
            ]);
            $styles = collect(['Salsa', 'Bachata'])->mapWithKeys(fn (string $name) => [
                Str::slug($name) => DanceStyle::query()->create(['organization_id' => $organization->id, 'name' => $name, 'slug' => Str::slug($name)]),
            ]);
            $levels = collect(['Essential', 'Basic', 'Intermediate', 'Open', 'Ensemble'])->mapWithKeys(fn (string $name, int $index) => [
                Str::slug($name) => Level::query()->create(['organization_id' => $organization->id, 'name' => $name, 'slug' => Str::slug($name), 'sort_order' => $index + 1]),
            ]);

            $people = [];
            $people['academy_admin'][] = $this->member($organization, 'Marina Admin', 'academy.admin@demo.local', $roles['academy_admin']);
            for ($i = 1; $i <= 6; $i++) {
                $email = $i === 1 ? 'teacher@demo.local' : "teacher{$i}@demo.local";
                $people['teacher'][] = $this->member($organization, "Docente Demo {$i}", $email, $roles['teacher']);
            }
            for ($i = 1; $i <= 2; $i++) {
                $people['assistant_teacher'][] = $this->member($organization, "Asistente Demo {$i}", "assistant{$i}@demo.local", $roles['assistant_teacher']);
                $email = $i === 1 ? 'moderator@demo.local' : 'moderator2@demo.local';
                $people['moderator'][] = $this->member($organization, "Moderador Demo {$i}", $email, $roles['moderator']);
            }
            for ($i = 1; $i <= 40; $i++) {
                $email = $i === 1 ? 'student@demo.local' : "student{$i}@demo.local";
                $people['student'][] = $this->member($organization, sprintf('Estudiante Demo %02d', $i), $email, $roles['student']);
            }

            OrganizationMembership::query()->where('user_id', $people['student'][38]->id)->update([
                'status' => 'inactive',
                'access_expires_at' => $this->baseDate()->subDay(),
            ]);
            OrganizationMembership::query()->where('user_id', $people['student'][39]->id)->update([
                'status' => 'reactivation_requested',
                'access_expires_at' => $this->baseDate()->subDays(10),
                'reactivation_requested_at' => $this->baseDate(),
            ]);
            $people['photographer'][] = $this->member($organization, 'Fotógrafa Demo', 'photographer@demo.local', $roles['photographer']);
            $people['videographer'][] = $this->member($organization, 'Videógrafo Demo', 'videographer@demo.local', $roles['videographer']);

            $groupSpecs = [
                ['Salsa Essential Centro', 'centro', 'salsa', 'essential'],
                ['Salsa Basic A', 'centro', 'salsa', 'basic'],
                ['Salsa Basic B', 'norte', 'salsa', 'basic'],
                ['Salsa Intermediate Tuesday', 'centro', 'salsa', 'intermediate'],
                ['Salsa Open', 'norte', 'salsa', 'open'],
                ['Salsa Ensemble 2026', 'centro', 'salsa', 'ensemble'],
                ['Bachata Essential', 'centro', 'bachata', 'essential'],
                ['Bachata Basic Norte', 'norte', 'bachata', 'basic'],
                ['Bachata Intermediate', 'centro', 'bachata', 'intermediate'],
                ['Bachata Open', 'norte', 'bachata', 'open'],
            ];

            $groups = collect($groupSpecs)->map(function (array $spec) use ($organization, $branches, $styles, $levels): DanceGroup {
                [$name, $branch, $style, $level] = $spec;

                return DanceGroup::query()->create([
                    'organization_id' => $organization->id,
                    'branch_id' => $branches[$branch]->id,
                    'dance_style_id' => $styles[$style]->id,
                    'level_id' => $levels[$level]->id,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'description' => "Espacio privado de {$name}.",
                    'settings' => ['allow_student_uploads' => true, 'student_uploads_require_approval' => true, 'allow_comments' => true],
                ]);
            });

            foreach ($groups as $index => $group) {
                $this->groupMember($organization, $group, $people['teacher'][$index % 6], 'teacher');
                if ($index < 2) {
                    $this->groupMember($organization, $group, $people['assistant_teacher'][$index], 'assistant_teacher');
                }
                if ($index < 2) {
                    $this->groupMember($organization, $group, $people['moderator'][$index], 'moderator');
                }
            }

            foreach ($people['student'] as $index => $student) {
                $this->groupMember($organization, $groups[$index % 10], $student, 'student');
                if ($index < 8 && $index % 10 !== 5) {
                    $this->groupMember($organization, $groups[5], $student, 'student');
                }
            }

            GroupMembership::query()->create([
                'organization_id' => $organization->id,
                'dance_group_id' => $groups[1]->id,
                'user_id' => $people['student'][0]->id,
                'responsibility' => 'student',
                'status' => 'ended',
                'started_at' => $this->baseDate()->subYear()->startOfDay(),
                'ended_at' => $this->baseDate()->subMonths(3)->endOfDay(),
                'assigned_by' => $people['academy_admin'][0]->id,
            ]);
        });
    }

    /** @param Collection<int, Permission> $permissions */
    private function seedSecondaryAcademy(Organization $organization, Collection $permissions): void
    {
        app(TenantContext::class)->run($organization->id, function () use ($organization, $permissions): void {
            $roles = $this->seedRoles($organization, $permissions);
            $admin = $this->member($organization, 'Admin Movimiento Norte', 'admin@tenant-b.demo.local', $roles['academy_admin']);
            $student = $this->member($organization, 'Estudiante Tenant B', 'student@tenant-b.demo.local', $roles['student']);
            $branch = Branch::query()->create(['organization_id' => $organization->id, 'name' => 'Principal', 'slug' => 'principal']);
            $style = DanceStyle::query()->create(['organization_id' => $organization->id, 'name' => 'Salsa', 'slug' => 'salsa']);
            $level = Level::query()->create(['organization_id' => $organization->id, 'name' => 'Basic', 'slug' => 'basic', 'sort_order' => 1]);
            $group = DanceGroup::query()->create([
                'organization_id' => $organization->id,
                'branch_id' => $branch->id,
                'dance_style_id' => $style->id,
                'level_id' => $level->id,
                'name' => 'Salsa Privado Tenant B',
                'slug' => 'salsa-privado-tenant-b',
                'settings' => ['allow_student_uploads' => false],
            ]);
            $this->groupMember($organization, $group, $admin, 'teacher');
            $this->groupMember($organization, $group, $student, 'student');
        });
    }

    /**
     * @param  Collection<int, Permission>  $permissions
     * @return array<string, Role>
     */
    private function seedRoles(Organization $organization, Collection $permissions): array
    {
        $roles = [];
        foreach ($this->rolePermissions as $key => $permissionKeys) {
            $role = Role::query()->create([
                'organization_id' => $organization->id,
                'key' => $key,
                'name' => str($key)->replace('_', ' ')->title(),
                'is_system' => true,
            ]);
            $permissions
                ->where('scope', 'tenant')
                ->whereIn('key', $permissionKeys)
                ->each(fn (Permission $permission) => $role->grantPermission($permission));
            $roles[$key] = $role;
        }

        return $roles;
    }

    private function member(Organization $organization, string $name, string $email, Role $role): User
    {
        $user = $this->user($name, $email);
        OrganizationMembership::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'status' => 'active',
            'access_expires_at' => $this->baseDate()->addMonths(3),
            'joined_at' => $this->baseDate()->subMonths(2),
        ]);
        RoleAssignment::query()->create([
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'assigned_at' => $this->baseDate(),
        ]);

        return $user;
    }

    private function user(string $name, string $email): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'email_verified_at' => $this->baseDate(),
            'password' => $this->demoPasswordHash ??= Hash::make(self::PASSWORD),
        ]);
    }

    private function groupMember(Organization $organization, DanceGroup $group, User $user, string $responsibility): void
    {
        GroupMembership::query()->create([
            'organization_id' => $organization->id,
            'dance_group_id' => $group->id,
            'user_id' => $user->id,
            'responsibility' => $responsibility,
            'status' => 'active',
            'started_at' => $this->baseDate()->subMonths(2)->startOfDay(),
        ]);
    }

    private function baseDate(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', self::BASE_DATE, 'UTC');
    }
}
