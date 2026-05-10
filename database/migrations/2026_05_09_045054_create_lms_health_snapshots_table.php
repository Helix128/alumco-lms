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
        Schema::create('lms_health_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('failed_jobs_count')->default(0);
            $table->unsignedInteger('pending_jobs_count')->default(0);
            $table->decimal('error_rate', 8, 2)->default(0);
            $table->unsignedInteger('active_users')->default(0);
            $table->timestamp('captured_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_health_snapshots');
    }
};
