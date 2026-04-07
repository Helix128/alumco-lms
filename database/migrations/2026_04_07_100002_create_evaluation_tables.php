<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Evaluaciones (una por módulo de tipo 'evaluacion')
        Schema::create('evaluaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->unsignedSmallInteger('puntos_aprobacion')->default(1);
            $table->timestamps();
        });

        // 2. Preguntas de la evaluación
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluacion_id')->constrained('evaluaciones')->cascadeOnDelete();
            $table->text('enunciado');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        // 3. Opciones de respuesta por pregunta
        Schema::create('opciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregunta_id')->constrained('preguntas')->cascadeOnDelete();
            $table->text('texto');
            $table->boolean('es_correcta')->default(false);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });

        // 4. Intentos de evaluación por usuario
        Schema::create('intentos_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluacion_id')->constrained('evaluaciones')->cascadeOnDelete();
            $table->unsignedSmallInteger('puntaje');
            $table->unsignedSmallInteger('total_preguntas');
            $table->boolean('aprobado')->default(false);
            $table->timestamps();
        });

        // 5. Respuestas individuales por intento
        Schema::create('respuestas_evaluacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intento_id')->constrained('intentos_evaluacion')->cascadeOnDelete();
            $table->foreignId('pregunta_id')->constrained('preguntas')->cascadeOnDelete();
            $table->foreignId('opcion_id')->constrained('opciones')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_evaluacion');
        Schema::dropIfExists('intentos_evaluacion');
        Schema::dropIfExists('opciones');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('evaluaciones');
    }
};
