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
        Schema::dropIfExists('consultas_curso');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('consultas_curso', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('respondido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->text('pregunta');
            $table->text('respuesta')->nullable();
            $table->string('visibilidad', 20)->default('publica'); // publica, privada
            $table->string('estado', 20)->default('pendiente'); // pendiente, respondida, cerrada
            $table->timestamp('respondido_at')->nullable();
            $table->timestamps();
        });
    }
};
