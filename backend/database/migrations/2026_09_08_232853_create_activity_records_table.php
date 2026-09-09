<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('label', 160);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'user_id', 'created_at']);
            $table->index(['organization_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_records');
    }
};
