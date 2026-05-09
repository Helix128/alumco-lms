<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('cursos', 'nota_capacitador')) {
            Schema::table('cursos', function (Blueprint $table): void {
                $table->text('nota_capacitador')->nullable()->after('descripcion');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table): void {
            $table->dropColumn('nota_capacitador');
        });
    }
};
