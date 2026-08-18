<?php

namespace Database\Seeders\Testing;

use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Evaluacion;
use App\Models\Modulo;
use App\Models\Opcion;
use App\Models\PlanificacionCurso;
use App\Models\Pregunta;
use App\Models\Sede;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class BrowserAuditSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Los datos de auditoría de navegador sólo pueden crearse en local o testing.');
        }

        $this->call(RolesAndPermissionsSeeder::class);

        $sede = Sede::query()->firstOrCreate(['nombre' => 'Sede auditoría']);
        $estamento = Estamento::query()->firstOrCreate(['nombre' => 'Estamento auditoría']);
        $password = Hash::make('password');

        $users = [
            'trabajador' => ['email' => 'auditoria.trabajador@alumco.local', 'name' => 'Colaborador Auditoría', 'rut' => '25.000.001-7', 'role' => 'Trabajador'],
            'capacitador' => ['email' => 'auditoria.capacitador@alumco.local', 'name' => 'Capacitador Auditoría', 'rut' => '25.000.002-5', 'role' => 'Capacitador Interno'],
            'administrador' => ['email' => 'auditoria.admin@alumco.local', 'name' => 'Administrador Auditoría', 'rut' => '25.000.003-3', 'role' => 'Administrador'],
            'desarrollador' => ['email' => 'auditoria.dev@alumco.local', 'name' => 'Desarrollador Auditoría', 'rut' => '25.000.004-1', 'role' => 'Desarrollador'],
        ];

        foreach ($users as $key => $attributes) {
            $role = $attributes['role'];
            unset($attributes['role']);

            $user = User::withTrashed()->updateOrCreate(
                ['email' => $attributes['email']],
                [...$attributes, 'password' => $password, 'activo' => true, 'sede_id' => $sede->id, 'estamento_id' => $estamento->id]
            );
            $user->restore();
            $user->syncRoles([$role]);
            $users[$key] = $user;
        }

        $course = Curso::withTrashed()->updateOrCreate(
            ['titulo' => 'Curso de auditoría heurística'],
            [
                'descripcion' => 'Contenido estable para comprobar navegación, accesibilidad, evaluaciones y estados del LMS.',
                'capacitador_id' => $users['capacitador']->id,
                'color_promedio' => '#075985',
            ]
        );
        $course->restore();
        $course->estamentos()->sync([$estamento->id]);

        $planning = PlanificacionCurso::withTrashed()->updateOrCreate(
            ['curso_id' => $course->id, 'notas' => 'Ventana de auditoría automática'],
            ['sede_id' => null, 'fecha_inicio' => now()->subMonth()->toDateString(), 'fecha_fin' => now()->addMonth()->toDateString()]
        );
        $planning->restore();

        $introModule = Modulo::withTrashed()->updateOrCreate(
            ['curso_id' => $course->id, 'orden' => 1],
            [
                'titulo' => 'Introducción accesible',
                'tipo_contenido' => 'texto',
                'contenido' => 'Este módulo permite comprobar lectura, jerarquía y navegación por teclado.',
                'duracion_minutos' => 5,
            ]
        );
        $introModule->restore();

        $evaluationModule = Modulo::withTrashed()->updateOrCreate(
            ['curso_id' => $course->id, 'orden' => 2],
            ['titulo' => 'Evaluación de auditoría', 'tipo_contenido' => 'evaluacion']
        );
        $evaluationModule->restore();
        $evaluation = Evaluacion::withTrashed()->updateOrCreate(
            ['modulo_id' => $evaluationModule->id],
            []
        );
        $evaluation->restore();
        $question = Pregunta::withTrashed()->updateOrCreate(
            ['evaluacion_id' => $evaluation->id, 'orden' => 1],
            ['enunciado' => '¿Qué comunica el estado actual de una operación?']
        );
        $question->restore();
        $correctOption = Opcion::withTrashed()->updateOrCreate(
            ['pregunta_id' => $question->id, 'orden' => 1],
            ['texto' => 'Un mensaje comprensible y accesible', 'es_correcta' => true]
        );
        $correctOption->restore();
        $incorrectOption = Opcion::withTrashed()->updateOrCreate(
            ['pregunta_id' => $question->id, 'orden' => 2],
            ['texto' => 'Únicamente un cambio de color', 'es_correcta' => false]
        );
        $incorrectOption->restore();
    }
}
