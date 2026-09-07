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
        DB::table('permission_platform_role')
            ->whereIn('permission_id', DB::table('permissions')->where('scope', '!=', 'platform')->select('id'))
            ->delete();

        Schema::table('permission_platform_role', function (Blueprint $table) {
            $table->enum('permission_scope', ['platform'])->default('platform');
            $table->foreign(['permission_id', 'permission_scope'], 'platform_role_platform_permission_foreign')
                ->references(['id', 'scope'])->on('permissions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $permissionRoles = DB::table('permission_platform_role')
                ->get(['permission_id', 'platform_role_id'])
                ->map(fn (object $row): array => [
                    'permission_id' => $row->permission_id,
                    'platform_role_id' => $row->platform_role_id,
                ])->all();

            Schema::drop('permission_platform_role');
            Schema::create('permission_platform_role', function (Blueprint $table) {
                $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
                $table->foreignId('platform_role_id')->constrained()->cascadeOnDelete();
                $table->primary(['permission_id', 'platform_role_id']);
            });

            if ($permissionRoles !== []) {
                DB::table('permission_platform_role')->insert($permissionRoles);
            }
        } else {
            Schema::table('permission_platform_role', function (Blueprint $table) {
                $table->dropForeign('platform_role_platform_permission_foreign');
                $table->dropColumn('permission_scope');
            });
        }
    }
};
