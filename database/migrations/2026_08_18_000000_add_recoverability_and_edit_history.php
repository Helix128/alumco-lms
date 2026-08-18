<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $recoverableTables = [
        'cursos',
        'seccion_cursos',
        'modulos',
        'planificaciones_cursos',
        'evaluaciones',
        'preguntas',
        'opciones',
        'reporte_presets',
    ];

    public function up(): void
    {
        foreach ($this->recoverableTables as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->softDeletes();
            });
        }

        Schema::create('edit_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('context', 40);
            $table->string('scope_id', 80);
            $table->string('label');
            $table->json('before_state');
            $table->json('after_state');
            $table->char('before_hash', 64);
            $table->char('after_hash', 64);
            $table->timestamp('undone_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'context', 'scope_id', 'expires_at'], 'edit_histories_scope_expiry_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edit_histories');

        foreach (array_reverse($this->recoverableTables) as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
