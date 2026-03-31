<?php

namespace Database\Seeders\Testing;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\ProgresoModulo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProgressSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@Alumco.cl');
        $users = User::query()->where('email', '!=', $adminEmail)->get();
        $cursos = Curso::query()->get();

        if ($users->isEmpty() || $cursos->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            $cantidadCursos = rand(1, min(3, $cursos->count()));
            $cursosInteractuados = $cursos->random($cantidadCursos);

            foreach ($cursosInteractuados as $curso) {
                $modulos = Modulo::query()
                    ->where('curso_id', $curso->id)
                    ->orderBy('orden')
                    ->get();

                if ($modulos->isEmpty()) {
                    continue;
                }

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
                    $modulosCompletados = rand(1, min(3, $modulos->count()));

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
        }
    }
}
