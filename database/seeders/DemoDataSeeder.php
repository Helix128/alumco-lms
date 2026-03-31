<?php

namespace Database\Seeders;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Estamento;
use App\Models\Modulo;
use App\Models\ProgresoModulo;
use App\Models\Sede;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $sedes = Sede::query()->get();
        $estamentos = Estamento::query()->get();

        if ($sedes->isEmpty()) {
            $sedes = collect([
                Sede::firstOrCreate(['nombre' => 'Concepcion']),
                Sede::firstOrCreate(['nombre' => 'Hualpen']),
                Sede::firstOrCreate(['nombre' => 'Coyhaique']),
            ]);
        }

        if ($estamentos->isEmpty()) {
            $estamentos = collect([
                Estamento::firstOrCreate(['nombre' => 'Profesionales']),
                Estamento::firstOrCreate(['nombre' => 'Auxiliares de servicio']),
                Estamento::firstOrCreate(['nombre' => 'Administracion']),
            ]);
        }

        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@Alumco.cl');
        $admin = User::query()->where('email', $adminEmail)->first();

        if (! $admin) {
            $admin = User::query()->firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => env('SEED_ADMIN_NAME', 'Admin Alumco'),
                    'password' => bcrypt(env('SEED_ADMIN_PASSWORD', 'password')),
                    'sede_id' => $sedes->first()->id,
                    'estamento_id' => $estamentos->first()->id,
                    'activo' => true,
                ]
            );
        }

        $cursosNombres = [
            'Infecciones Intrahospitalarias',
            'Manejo de Residuos (REAS)',
            'RCP Basico',
            'Ley de Derechos del Paciente',
            'Higiene de Manos',
        ];

        $cursos = collect();

        foreach ($cursosNombres as $nombre) {
            $curso = Curso::query()->updateOrCreate(
                ['titulo' => $nombre],
                [
                    'descripcion' => 'Curso obligatorio de capacitacion continua.',
                    'fecha_inicio' => now()->subMonths(6),
                    'fecha_fin' => now()->addMonths(6),
                    'capacitador_id' => $admin->id,
                ]
            );

            $cursos->push($curso);

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

        User::factory(50)->create()->each(function (User $user) use ($sedes, $estamentos, $cursos): void {
            $user->update([
                'sede_id' => $sedes->random()->id,
                'estamento_id' => $estamentos->random()->id,
            ]);

            $cantidadCursos = rand(1, min(3, $cursos->count()));
            $cursosInteractuados = $cursos->random($cantidadCursos);

            foreach ($cursosInteractuados as $curso) {
                $modulos = Modulo::query()
                    ->where('curso_id', $curso->id)
                    ->orderBy('orden')
                    ->get();

                $loTermino = rand(0, 1);

                if ($loTermino === 1) {
                    foreach ($modulos as $modulo) {
                        ProgresoModulo::query()->updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'modulo_id' => $modulo->id,
                            ],
                            [
                                'completado' => true,
                                'fecha_completado' => now()->subDays(rand(1, 30)),
                            ]
                        );
                    }

                    Certificado::query()->firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'curso_id' => $curso->id,
                        ],
                        [
                            'codigo_verificacion' => (string) Str::uuid(),
                            'ruta_pdf' => '/certificados/demo.pdf',
                            'fecha_emision' => now()->subDays(rand(1, 30)),
                        ]
                    );
                } else {
                    $modulosCompletados = rand(1, 3);

                    for ($i = 0; $i < $modulosCompletados; $i++) {
                        ProgresoModulo::query()->updateOrCreate(
                            [
                                'user_id' => $user->id,
                                'modulo_id' => $modulos[$i]->id,
                            ],
                            [
                                'completado' => true,
                                'fecha_completado' => now()->subDays(rand(1, 10)),
                            ]
                        );
                    }
                }
            }
        });
    }
}