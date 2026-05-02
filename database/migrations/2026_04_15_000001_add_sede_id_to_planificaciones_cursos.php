<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planificaciones_cursos', function (Blueprint $table) {
            $table->foreignId('sede_id')
                ->nullable()
                ->after('curso_id')
                ->constrained('sedes')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('planificaciones_cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sede_id');
        });
    }
};
