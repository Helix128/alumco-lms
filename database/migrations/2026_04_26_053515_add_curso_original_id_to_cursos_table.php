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
        Schema::table('cursos', function (Blueprint $table) {
            $table->foreignId('curso_original_id')->after('capacitador_id')->nullable()->constrained('cursos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curso_original_id');
        });
    }
};
