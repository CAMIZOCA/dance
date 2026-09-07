<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 7)->default('#7C3AED');
            $table->string('secondary_color', 7)->default('#111827');
            $table->string('whatsapp_number', 32)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('subdomain')->nullable()->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('active');
            $table->timestamp('access_expires_at')->nullable();
            $table->timestamp('reactivation_requested_at')->nullable();
            $table->timestamp('reactivated_at')->nullable();
            $table->timestamp('joined_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'user_id']);
            $table->index(['organization_id', 'status', 'access_expires_at']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->boolean('is_system')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'key']);
            $table->unique(['organization_id', 'id']);
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('role_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('scope_type')->nullable();
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->timestamp('assigned_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['organization_id', 'user_id', 'revoked_at']);
            $table->index(['scope_type', 'scope_id']);
            $table->foreign(['organization_id', 'user_id'])->references(['organization_id', 'user_id'])->on('organization_user')->cascadeOnDelete();
            $table->foreign(['organization_id', 'role_id'])->references(['organization_id', 'id'])->on('roles')->cascadeOnDelete();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'slug']);
            $table->unique(['organization_id', 'id']);
        });

        Schema::create('dance_styles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'slug']);
            $table->unique(['organization_id', 'id']);
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['organization_id', 'slug']);
            $table->unique(['organization_id', 'id']);
        });

        Schema::create('dance_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('dance_style_id');
            $table->unsignedBigInteger('level_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(true);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['organization_id', 'slug']);
            $table->unique(['organization_id', 'id']);
            $table->foreign(['organization_id', 'branch_id'])->references(['organization_id', 'id'])->on('branches')->restrictOnDelete();
            $table->foreign(['organization_id', 'dance_style_id'])->references(['organization_id', 'id'])->on('dance_styles')->restrictOnDelete();
            $table->foreign(['organization_id', 'level_id'])->references(['organization_id', 'id'])->on('levels')->restrictOnDelete();
        });

        Schema::create('group_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('dance_group_id');
            $table->unsignedBigInteger('user_id');
            $table->string('responsibility', 32);
            $table->string('status', 32)->default('active');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign(['organization_id', 'dance_group_id'])->references(['organization_id', 'id'])->on('dance_groups')->cascadeOnDelete();
            $table->foreign(['organization_id', 'user_id'])->references(['organization_id', 'user_id'])->on('organization_user')->cascadeOnDelete();
            $table->index(['organization_id', 'dance_group_id', 'status']);
            $table->index(['organization_id', 'user_id', 'status']);
            $table->unique(['dance_group_id', 'user_id', 'responsibility', 'started_at'], 'group_membership_history_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_memberships');
        Schema::dropIfExists('dance_groups');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('dance_styles');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('role_assignments');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
