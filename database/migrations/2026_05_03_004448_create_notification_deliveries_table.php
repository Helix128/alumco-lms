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
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('planificacion_curso_id')->nullable()->constrained('planificaciones_cursos')->cascadeOnDelete();
            $table->foreignId('certificado_id')->nullable()->constrained('certificados')->cascadeOnDelete();
            $table->string('type', 80);
            $table->string('dedupe_key')->unique();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['user_id', 'curso_id', 'type']);
            $table->index(['type', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
    }
};
