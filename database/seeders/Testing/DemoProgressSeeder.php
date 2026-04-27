<?php

namespace Database\Seeders\Testing;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\Modulo;
use App\Models\ProgresoModulo;
use App\Models\User;
use App\Models\IntentoEvaluacion;
use App\Services\CertificadoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoProgressSeeder extends Seeder
{
    public function run(CertificadoService $certificadoService): void
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
                    ->with('evaluacion')
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

                        if ($modulo->tipo_contenido === 'evaluacion' && $modulo->evaluacion) {
                            IntentoEvaluacion::query()->firstOrCreate(
                                [
                                    'user_id' => $user->id,
                                    'evaluacion_id' => $modulo->evaluacion->id,
                                ],
                                [
                                    'puntaje' => $modulo->evaluacion->puntos_aprobacion,
                                    'total_preguntas' => max($modulo->evaluacion->puntos_aprobacion, 1),
                                    'aprobado' => true,
                                    'created_at' => now()->subDays(rand(1, 30)),
                                    'updated_at' => now()->subDays(rand(1, 30)),
                                ]
                            );
                        }
                    }

                    try {
                        $certificadoService->generarParaUsuario($user, $curso);
                    } catch (\Throwable $e) {
                        // Si falla la generación oficial (ej. por validaciones del service),
                        // creamos un registro de fallback para que no quede vacío.
                        Certificado::query()->firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'curso_id' => $curso->id,
                            ],
                            [
                                'codigo_verificacion' => (string) Str::uuid(),
                                'ruta_pdf' => 'certificados/demo.pdf', // Sin slash inicial para consistencia
                                'fecha_emision' => now()->subDays(rand(1, 30)),
                            ]
                        );
                    }
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
