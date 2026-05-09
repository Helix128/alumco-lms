<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feedbacks')) {
            return;
        }
        Schema::create('feedbacks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->cascadeOnDelete();
            $table->foreignId('modulo_id')->nullable()->constrained('modulos')->nullOnDelete();
            $table->string('tipo', 20);
            $table->string('categoria', 40);
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('mensaje')->nullable();
            $table->string('estado', 20)->default('nuevo');
            $table->timestamps();

            $table->index(['tipo', 'estado']);
            $table->index(['curso_id', 'tipo']);
            // Un feedback de curso por trabajador evita duplicados al reabrir el formulario.
            $table->unique(['user_id', 'curso_id', 'tipo'], 'feedbacks_user_course_type_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
