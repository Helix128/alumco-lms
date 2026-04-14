<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cursos')
            ->whereNotNull('fecha_inicio')
            ->whereNotNull('fecha_fin')
            ->orderBy('id')
            ->each(function ($curso) {
                DB::table('planificaciones_cursos')->insert([
                    'curso_id'    => $curso->id,
                    'fecha_inicio' => $curso->fecha_inicio,
                    'fecha_fin'   => $curso->fecha_fin,
                    'notas'       => null,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            });

        // Pasar a nullable para no romper vistas que aún las leen
        Schema::table('cursos', function (Blueprint $table) {
            $table->date('fecha_inicio')->nullable()->change();
            $table->date('fecha_fin')->nullable()->change();
        });
    }

    public function down(): void
    {
        $planificaciones = DB::table('planificaciones_cursos')
            ->orderBy('curso_id')
            ->orderBy('fecha_inicio')
            ->get()
            ->unique('curso_id');

        foreach ($planificaciones as $p) {
            DB::table('cursos')->where('id', $p->curso_id)->update([
                'fecha_inicio' => $p->fecha_inicio,
                'fecha_fin'   => $p->fecha_fin,
            ]);
        }

        DB::table('planificaciones_cursos')->delete();
    }
};
