<?php

namespace Database\Seeders\Testing;

use App\Models\Curso;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCoursesSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@Alumco.cl');
        $admin = User::query()->where('email', $adminEmail)->first();

        if (! $admin) {
            return;
        }

        $cursosNombres = [
            'Infecciones Intrahospitalarias',
            'Manejo de Residuos (REAS)',
            'RCP Basico',
            'Ley de Derechos del Paciente',
            'Higiene de Manos',
        ];

        foreach ($cursosNombres as $nombre) {
            $curso = Curso::query()->updateOrCreate(
                ['titulo' => $nombre],
                [
                    'descripcion' => 'Curso obligatorio de capacitacion continua.',
                    'capacitador_id' => $admin->id,
                ]
            );

            for ($i = 1; $i <= 4; $i++) {
                Modulo::query()->updateOrCreate(
                    [
                        'curso_id' => $curso->id,
                        'orden' => $i,
                    ],
                    [
                        'titulo' => "Modulo {$i}: Contenido del curso",
                        'tipo_contenido' => 'video',
                        'ruta_archivo' => null,
                    ]
                );
            }
        }
    }
}
