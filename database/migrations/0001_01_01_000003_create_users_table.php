<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. TABLAS INDEPENDIENTES (Sin claves foráneas)
        Schema::create('sedes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        Schema::create('estamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        // 2. USUARIOS Y SISTEMA LARAVEL (Depende de sedes y estamentos)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('sexo', ['F', 'M', 'Otro'])->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('sede_id')->nullable()->constrained('sedes')->nullOnDelete();
            $table->foreignId('estamento_id')->nullable()->constrained('estamentos')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // 3. CURSOS (Depende de users)
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->string('imagen_portada')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->foreignId('capacitador_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('curso_estamento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estamento_id')->constrained()->cascadeOnDelete();
        });

        // 4. MÓDULOS (Depende de cursos)
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->string('titulo');
            $table->integer('orden');
            $table->enum('tipo_contenido', ['video', 'pdf', 'ppt']);
            $table->string('ruta_archivo')->nullable();
            $table->timestamps();
        });

        Schema::create('progresos_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained()->cascadeOnDelete();
            $table->boolean('completado')->default(false);
            $table->timestamp('fecha_completado')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'modulo_id']);
        });

        // 5. CERTIFICADOS
        Schema::create('certificados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained()->cascadeOnDelete();
            $table->string('codigo_verificacion')->unique();
            $table->string('ruta_pdf');
            $table->timestamp('fecha_emision')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // El orden de borrado es inverso a la creación
        Schema::dropIfExists('certificados');
        Schema::dropIfExists('progresos_modulo');
        Schema::dropIfExists('modulos');
        Schema::dropIfExists('curso_estamento');
        Schema::dropIfExists('cursos');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('estamentos');
        Schema::dropIfExists('sedes');
    }
};