<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('system_task_runs')) {
            return;
        }
        Schema::create('system_task_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('command', 120);
            $table->string('status', 20);
            $table->unsignedInteger('processed_count')->default(0);
            $table->text('summary')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['command', 'started_at']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_task_runs');
    }
};
