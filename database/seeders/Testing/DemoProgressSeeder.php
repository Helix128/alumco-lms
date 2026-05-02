<?php

namespace Database\Seeders\Testing;

use App\Models\Certificado;
use App\Models\Curso;
use App\Models\IntentoEvaluacion;
use App\Models\Modulo;
use App\Models\ProgresoModulo;
use App\Models\RespuestaEvaluacion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoProgressSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('SEED_ADMIN_EMAIL', 'admin@alumco.cl');
        $users = User::query()
            ->where('email', '!=', $adminEmail)
            ->where('email', 'like', 'trabajador.demo.%@alumco.local')
            ->orderBy('id')
            ->get();

        $cursos = Curso::query()
            ->with(['modulos.evaluacion.preguntas.opciones'])
            ->orderBy('id')
            ->get();

        if ($users->isEmpty() || $cursos->isEmpty()) {
            return;
        }

        foreach ($users as $userIndex => $user) {
            $cursosAsignados = $this->coursesForUser($cursos, $userIndex);

            foreach ($cursosAsignados as $courseIndex => $curso) {
                $modulos = $curso->modulos->sortBy('orden')->values();

                if ($modulos->isEmpty()) {
                    continue;
                }

                $completed = ($userIndex + $courseIndex) % 3 === 0;
                $completedCount = $completed
                    ? $modulos->count()
                    : min($modulos->count() - 1, (($userIndex + $courseIndex) % max(1, $modulos->count() - 1)) + 1);

                $this->markProgress($user->id, $modulos->take($completedCount));

                if ($completed) {
                    $this->createApprovedAttempts($user->id, $modulos);
                    $this->createDemoCertificate($user->id, $curso->id);
                }
            }
        }
    }

    /**
     * @param  Collection<int, Curso>  $cursos
     * @return Collection<int, Curso>
     */
    private function coursesForUser(Collection $cursos, int $userIndex): Collection
    {
        $count = min(3, $cursos->count());
        $offset = $userIndex % $cursos->count();

        return $cursos
            ->slice($offset)
            ->concat($cursos->slice(0, $offset))
            ->take($count)
            ->values();
    }

    /**
     * @param  Collection<int, Modulo>  $modulos
     */
    private function markProgress(int $userId, Collection $modulos): void
    {
        foreach ($modulos as $index => $modulo) {
            ProgresoModulo::query()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'modulo_id' => $modulo->id,
                ],
                [
                    'completado' => true,
                    'fecha_completado' => now()->subDays(30 - min($index, 29)),
                ]
            );
        }
    }

    /**
     * @param  Collection<int, Modulo>  $modulos
     */
    private function createApprovedAttempts(int $userId, Collection $modulos): void
    {
        foreach ($modulos as $modulo) {
            if ($modulo->tipo_contenido !== 'evaluacion' || ! $modulo->evaluacion) {
                continue;
            }

            $preguntas = $modulo->evaluacion->preguntas;
            $total = $preguntas->count();

            if ($total === 0) {
                continue;
            }

            $intento = IntentoEvaluacion::query()->updateOrCreate(
                [
                    'user_id' => $userId,
                    'evaluacion_id' => $modulo->evaluacion->id,
                ],
                [
                    'puntaje' => $total,
                    'total_preguntas' => $total,
                    'aprobado' => true,
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]
            );

            foreach ($preguntas as $pregunta) {
                $opcionCorrecta = $pregunta->opciones->firstWhere('es_correcta', true);

                if (! $opcionCorrecta) {
                    continue;
                }

                RespuestaEvaluacion::query()->firstOrCreate([
                    'intento_id' => $intento->id,
                    'pregunta_id' => $pregunta->id,
                    'opcion_id' => $opcionCorrecta->id,
                ]);
            }
        }
    }

    private function createDemoCertificate(int $userId, int $cursoId): void
    {
        Certificado::query()->firstOrCreate(
            [
                'user_id' => $userId,
                'curso_id' => $cursoId,
            ],
            [
                'codigo_verificacion' => (string) Str::uuid(),
                'ruta_pdf' => 'certificados/demo.pdf',
                'fecha_emision' => now()->subDay(),
            ]
        );
    }
}
