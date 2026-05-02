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
        Schema::create('seccion_cursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::table('modulos', function (Blueprint $table) {
            $table->foreignId('seccion_id')->nullable()->constrained('seccion_cursos')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('seccion_id');
        });
        Schema::dropIfExists('seccion_cursos');
    }
};
