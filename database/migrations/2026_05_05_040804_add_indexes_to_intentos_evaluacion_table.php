<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('intentos_evaluacion', function (Blueprint $table) {
            // Acelera el gate semanal: WHERE user_id = ? AND evaluacion_id = ? AND created_at >= ?
            $table->index(['user_id', 'evaluacion_id', 'created_at'], 'ie_user_evaluacion_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('intentos_evaluacion', function (Blueprint $table) {
            $table->dropIndex('ie_user_evaluacion_created_at');
        });
    }
};
