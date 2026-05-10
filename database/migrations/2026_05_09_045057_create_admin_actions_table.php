<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_actions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action', 120);
            $table->string('target_type', 80)->nullable();
            $table->string('target_id', 120)->nullable();
            $table->string('status', 20);
            $table->longText('output')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('executed_at')->index();
            $table->timestamps();

            $table->index(['action', 'executed_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_actions');
    }
};
