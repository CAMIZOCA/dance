<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_system')->default(true);
            $table->timestamps();
        });

        Schema::create('permission_platform_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'platform_role_id']);
        });

        Schema::create('platform_role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_role_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'revoked_at']);
        });

        $globalRoles = DB::table('roles')->whereNull('organization_id')->get();

        foreach ($globalRoles as $globalRole) {
            $platformRoleId = DB::table('platform_roles')->insertGetId([
                'key' => $globalRole->key,
                'name' => $globalRole->name,
                'is_system' => $globalRole->is_system,
                'created_at' => $globalRole->created_at,
                'updated_at' => $globalRole->updated_at,
            ]);

            $permissionRows = DB::table('permission_role')
                ->where('role_id', $globalRole->id)
                ->pluck('permission_id')
                ->map(fn (int $permissionId): array => [
                    'permission_id' => $permissionId,
                    'platform_role_id' => $platformRoleId,
                ])->all();

            if ($permissionRows !== []) {
                DB::table('permission_platform_role')->insert($permissionRows);
            }

            $assignmentRows = DB::table('role_assignments')
                ->whereNull('organization_id')
                ->where('role_id', $globalRole->id)
                ->get()
                ->map(fn (object $assignment): array => [
                    'user_id' => $assignment->user_id,
                    'platform_role_id' => $platformRoleId,
                    'assigned_at' => $assignment->assigned_at,
                    'revoked_at' => $assignment->revoked_at,
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ])->all();

            if ($assignmentRows !== []) {
                DB::table('platform_role_assignments')->insert($assignmentRows);
            }
        }

        DB::table('role_assignments')->whereNull('organization_id')->delete();
        DB::table('roles')->whereNull('organization_id')->delete();

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });

        foreach (DB::table('platform_roles')->get() as $platformRole) {
            $roleId = DB::table('roles')->insertGetId([
                'organization_id' => null,
                'key' => $platformRole->key,
                'name' => $platformRole->name,
                'is_system' => $platformRole->is_system,
                'created_at' => $platformRole->created_at,
                'updated_at' => $platformRole->updated_at,
            ]);

            $permissionRows = DB::table('permission_platform_role')
                ->where('platform_role_id', $platformRole->id)
                ->pluck('permission_id')
                ->map(fn (int $permissionId): array => ['permission_id' => $permissionId, 'role_id' => $roleId])
                ->all();

            if ($permissionRows !== []) {
                DB::table('permission_role')->insert($permissionRows);
            }

            $assignmentRows = DB::table('platform_role_assignments')
                ->where('platform_role_id', $platformRole->id)
                ->get()
                ->map(fn (object $assignment): array => [
                    'organization_id' => null,
                    'user_id' => $assignment->user_id,
                    'role_id' => $roleId,
                    'scope_type' => null,
                    'scope_id' => null,
                    'assigned_at' => $assignment->assigned_at,
                    'revoked_at' => $assignment->revoked_at,
                    'created_at' => $assignment->created_at,
                    'updated_at' => $assignment->updated_at,
                ])->all();

            if ($assignmentRows !== []) {
                DB::table('role_assignments')->insert($assignmentRows);
            }
        }

        Schema::dropIfExists('platform_role_assignments');
        Schema::dropIfExists('permission_platform_role');
        Schema::dropIfExists('platform_roles');
    }
};
