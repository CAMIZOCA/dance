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
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('scope', 16)->default('tenant')->after('description');
            $table->unique(['id', 'scope']);
        });

        DB::table('permissions')->where('key', 'platform.manage')->update(['scope' => 'platform']);

        Schema::table('permission_role', function (Blueprint $table) {
            $table->enum('permission_scope', ['tenant'])->default('tenant');
            $table->foreign(['permission_id', 'permission_scope'], 'permission_role_tenant_permission_foreign')
                ->references(['id', 'scope'])->on('permissions')->cascadeOnDelete();
        });

        $invalidScopeCount = DB::table('role_assignments')
            ->where(fn ($query) => $query
                ->whereNull('scope_type')
                ->whereNotNull('scope_id'))
            ->orWhere(fn ($query) => $query
                ->whereNotNull('scope_type')
                ->whereNull('scope_id'))
            ->count();

        if ($invalidScopeCount > 0) {
            throw new RuntimeException('Role assignments contain incomplete scope pairs.');
        }

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->unique(['organization_id', 'id']);
        });

        Schema::create('role_assignment_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('role_assignment_id')->unique();
            $table->string('scope_type', 64);
            $table->unsignedBigInteger('scope_id');
            $table->timestamps();
            $table->foreign(['organization_id', 'role_assignment_id'], 'role_assignment_scopes_tenant_assignment_foreign')
                ->references(['organization_id', 'id'])->on('role_assignments')->cascadeOnDelete();
            $table->index(['organization_id', 'scope_type', 'scope_id']);
        });

        $scopeRows = DB::table('role_assignments')
            ->whereNotNull('scope_type')
            ->whereNotNull('scope_id')
            ->get()
            ->map(fn (object $assignment): array => [
                'organization_id' => $assignment->organization_id,
                'role_assignment_id' => $assignment->id,
                'scope_type' => $assignment->scope_type,
                'scope_id' => $assignment->scope_id,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->updated_at,
            ])->all();

        if ($scopeRows !== []) {
            DB::table('role_assignment_scopes')->insert($scopeRows);
        }

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->dropIndex(['scope_type', 'scope_id']);
            $table->dropColumn(['scope_type', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::table('role_assignments', function (Blueprint $table) {
            $table->string('scope_type')->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->index(['scope_type', 'scope_id']);
        });

        foreach (DB::table('role_assignment_scopes')->get() as $scope) {
            DB::table('role_assignments')->where('id', $scope->role_assignment_id)->update([
                'scope_type' => $scope->scope_type,
                'scope_id' => $scope->scope_id,
            ]);
        }

        Schema::dropIfExists('role_assignment_scopes');

        Schema::table('role_assignments', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'id']);
        });

        if (DB::getDriverName() === 'sqlite') {
            $permissionRoles = DB::table('permission_role')
                ->get(['permission_id', 'role_id'])
                ->map(fn (object $row): array => [
                    'permission_id' => $row->permission_id,
                    'role_id' => $row->role_id,
                ])->all();

            Schema::drop('permission_role');
            Schema::create('permission_role', function (Blueprint $table) {
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('role_id')->constrained()->cascadeOnDelete();
                $table->primary(['permission_id', 'role_id']);
            });

            if ($permissionRoles !== []) {
                DB::table('permission_role')->insert($permissionRoles);
            }
        } else {
            Schema::table('permission_role', function (Blueprint $table) {
                $table->dropForeign('permission_role_tenant_permission_foreign');
                $table->dropColumn('permission_scope');
            });
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['id', 'scope']);
            $table->dropColumn('scope');
        });
    }
};
