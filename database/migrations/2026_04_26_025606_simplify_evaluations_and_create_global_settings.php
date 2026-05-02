<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Crear tabla de configuraciones globales (clave-valor)
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Insertar valores por defecto según lo solicitado
        DB::table('global_settings')->insert([
            [
                'key' => 'evaluacion_puntos_aprobacion',
                'value' => '70',
                'description' => 'Porcentaje de puntos necesarios para aprobar una evaluación (0-100).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'evaluacion_max_intentos_semanales',
                'value' => '3',
                'description' => 'Cantidad máxima de intentos permitidos por semana para un usuario en una evaluación.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Eliminar columnas configurables de la tabla evaluaciones
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->dropColumn(['puntos_aprobacion', 'max_intentos_semanales']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluaciones', function (Blueprint $table) {
            $table->integer('puntos_aprobacion')->default(70);
            $table->integer('max_intentos_semanales')->default(3);
        });

        Schema::dropIfExists('global_settings');
    }
};
