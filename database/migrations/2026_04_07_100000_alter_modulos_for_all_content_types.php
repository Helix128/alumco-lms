<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ampliar el ENUM para soportar todos los tipos de cápsula
        DB::statement("ALTER TABLE modulos MODIFY COLUMN tipo_contenido
            ENUM('video','pdf','ppt','texto','imagen','evaluacion') NOT NULL");

        Schema::table('modulos', function (Blueprint $table) {
            $table->longText('contenido')->nullable()->after('ruta_archivo');
            $table->unsignedSmallInteger('duracion_minutos')->nullable()->after('contenido');
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->dropColumn(['contenido', 'duracion_minutos']);
        });

        DB::statement("ALTER TABLE modulos MODIFY COLUMN tipo_contenido
            ENUM('video','pdf','ppt') NOT NULL");
    }
};
