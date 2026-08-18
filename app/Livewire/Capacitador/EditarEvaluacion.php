<?php

namespace App\Livewire\Capacitador;

use App\Exceptions\EditHistoryConflict;
use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\GlobalSetting;
use App\Models\Opcion;
use App\Models\Pregunta;
use App\Services\History\EditHistoryService;
use Closure;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditarEvaluacion extends Component
{
    public Evaluacion $evaluacion;

    public Curso $curso;

    public array $preguntas = [];

    public string $nuevaPreguntaEnunciado = '';

    public string $flashMensaje = '';

    public ?int $deletingPreguntaId = null;

    public ?int $deletingOpcionId = null;

    public string $deletingType = '';

    public function mount(Evaluacion $evaluacion, Curso $curso): void
    {
        abort_unless($evaluacion->modulo?->curso_id === $curso->id, 404);
        Gate::authorize('manage', $curso);
        $this->evaluacion = $evaluacion;
        $this->curso = $curso;
        $this->cargarPreguntas();
    }

    private function cargarPreguntas(): void
    {
        $this->preguntas = $this->evaluacion->preguntas()
            ->with('opciones')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'enunciado' => $p->enunciado,
                'orden' => $p->orden,
                'opciones' => $p->opciones->map(fn ($o) => [
                    'id' => $o->id,
                    'texto' => $o->texto,
                    'es_correcta' => (bool) $o->es_correcta,
                    'orden' => $o->orden,
                ])->values()->toArray(),
            ])
            ->values()
            ->toArray();
    }

    #[Computed]
    public function resumen(): array
    {
        $total = count($this->preguntas);
        $porcentaje = (int) GlobalSetting::get('evaluacion_puntos_aprobacion', 70);
        $puntosNecesarios = $total > 0 ? max(1, (int) ceil($total * ($porcentaje / 100))) : 0;
        $preguntasSinOpciones = 0;
        $preguntasSinCorrecta = 0;

        foreach ($this->preguntas as $p) {
            if (count($p['opciones']) === 0) {
                $preguntasSinOpciones++;
            } elseif (! collect($p['opciones'])->contains('es_correcta', true)) {
                $preguntasSinCorrecta++;
            }
        }

        return compact('total', 'puntosNecesarios', 'porcentaje', 'preguntasSinOpciones', 'preguntasSinCorrecta');
    }

    public function agregarPregunta(): void
    {
        $this->validate(['nuevaPreguntaEnunciado' => 'required|string|min:3']);

        $this->mutate('Agregar pregunta', function (): void {
            $pregunta = Pregunta::create([
                'evaluacion_id' => $this->evaluacion->id,
                'enunciado' => $this->nuevaPreguntaEnunciado,
                'orden' => count($this->preguntas) + 1,
            ]);

            $this->preguntas[] = [
                'id' => $pregunta->id,
                'enunciado' => $pregunta->enunciado,
                'orden' => $pregunta->orden,
                'opciones' => [],
            ];

            $this->nuevaPreguntaEnunciado = '';
        });
        $this->flash('Pregunta agregada.');
    }

    public function eliminarPregunta(int $preguntaId): void
    {
        $this->mutate('Eliminar pregunta', function () use ($preguntaId): void {
            Pregunta::query()
                ->whereKey($preguntaId)
                ->where('evaluacion_id', $this->evaluacion->id)
                ->delete();

            $this->preguntas = array_values(
                array_filter($this->preguntas, fn ($question) => $question['id'] !== $preguntaId)
            );

            foreach ($this->preguntas as $index => &$question) {
                $question['orden'] = $index + 1;
            }
        });
        $this->flash('Pregunta eliminada. Puedes deshacer este cambio.');
    }

    public function agregarOpcion(int $preguntaId): void
    {
        abort_unless(
            Pregunta::where('id', $preguntaId)->where('evaluacion_id', $this->evaluacion->id)->exists(),
            403
        );

        $newOrden = 1;
        foreach ($this->preguntas as $p) {
            if ($p['id'] === $preguntaId) {
                $newOrden = count($p['opciones']) + 1;
                break;
            }
        }

        $this->mutate('Agregar opción de respuesta', function () use ($preguntaId, $newOrden): void {
            $opcion = Opcion::create([
                'pregunta_id' => $preguntaId,
                'texto' => '',
                'es_correcta' => false,
                'orden' => $newOrden,
            ]);

            foreach ($this->preguntas as &$question) {
                if ($question['id'] === $preguntaId) {
                    $question['opciones'][] = [
                        'id' => $opcion->id,
                        'texto' => '',
                        'es_correcta' => false,
                        'orden' => $newOrden,
                    ];
                    break;
                }
            }
        });

        $this->dispatch('opcion-agregada', preguntaId: $preguntaId);
    }

    public function eliminarOpcion(int $opcionId): void
    {
        $this->mutate('Eliminar opción de respuesta', function () use ($opcionId): void {
            Opcion::query()
                ->whereKey($opcionId)
                ->whereHas('pregunta', fn ($query) => $query->where('evaluacion_id', $this->evaluacion->id))
                ->delete();

            foreach ($this->preguntas as &$question) {
                $question['opciones'] = array_values(
                    array_filter($question['opciones'], fn ($option) => $option['id'] !== $opcionId)
                );
            }
        });
        $this->flash('Opción eliminada. Puedes deshacer este cambio.');
    }

    public function toggleCorrecta(int $opcionId): void
    {
        $this->mutate('Cambiar respuesta correcta', function () use ($opcionId): void {
            foreach ($this->preguntas as &$question) {
                foreach ($question['opciones'] as $option) {
                    if ($option['id'] === $opcionId) {
                        foreach ($question['opciones'] as &$candidate) {
                            $candidate['es_correcta'] = ($candidate['id'] === $opcionId);
                            Opcion::where('id', $candidate['id'])->update(['es_correcta' => $candidate['es_correcta']]);
                        }
                        break 2;
                    }
                }
            }
        });
    }

    public function guardarEnunciado(int $preguntaId): void
    {
        $index = $this->indexFor($preguntaId);
        $this->validate(["preguntas.{$index}.enunciado" => 'required|string|min:3']);

        $this->mutate('Editar enunciado', fn () => Pregunta::where('id', $preguntaId)->update([
            'enunciado' => $this->preguntas[$index]['enunciado'],
        ]));

        $this->flash('Pregunta guardada.');
    }

    public function guardarTextoOpcion(int $opcionId): void
    {
        foreach ($this->preguntas as $question) {
            foreach ($question['opciones'] as $option) {
                if ($option['id'] === $opcionId) {
                    $this->mutate(
                        'Editar opción de respuesta',
                        fn () => Opcion::where('id', $opcionId)->update(['texto' => $option['texto']]),
                    );
                    $this->flash('Opción guardada.');

                    return;
                }
            }
        }
    }

    public function reordenarPreguntas(array $orden): void
    {
        $this->mutate('Reordenar preguntas', function () use ($orden): void {
            foreach ($orden as $index => $preguntaId) {
                Pregunta::where('id', $preguntaId)
                    ->where('evaluacion_id', $this->evaluacion->id)
                    ->update(['orden' => $index + 1]);
            }

            $indexed = collect($this->preguntas)->keyBy('id');
            $this->preguntas = collect($orden)
                ->map(fn ($id) => $indexed[$id] ?? null)
                ->filter()
                ->values()
                ->map(function ($question, $index) {
                    $question['orden'] = $index + 1;

                    return $question;
                })
                ->toArray();
        });
        $this->flash('Orden actualizado.');
    }

    private function indexFor(int $preguntaId): int
    {
        foreach ($this->preguntas as $i => $p) {
            if ($p['id'] === $preguntaId) {
                return $i;
            }
        }

        return 0;
    }

    private function flash(string $mensaje): void
    {
        $this->flashMensaje = $mensaje;
        $this->dispatch('flash-guardado');
    }

    /** @return array{can_undo: bool, can_redo: bool, undo_label: ?string, redo_label: ?string} */
    #[Computed]
    public function historyState(): array
    {
        return app(EditHistoryService::class)->availability(
            auth()->user(),
            EditHistoryService::Evaluation,
            $this->evaluacion->id,
        );
    }

    public function deshacer(): void
    {
        $this->travelHistory(false);
    }

    public function rehacer(): void
    {
        $this->travelHistory(true);
    }

    private function travelHistory(bool $redo): void
    {
        try {
            $step = $redo
                ? app(EditHistoryService::class)->redo(auth()->user(), EditHistoryService::Evaluation, $this->evaluacion->id)
                : app(EditHistoryService::class)->undo(auth()->user(), EditHistoryService::Evaluation, $this->evaluacion->id);
            $this->cargarPreguntas();
            unset($this->historyState);
            $this->flash(($redo ? 'Cambio rehecho: ' : 'Cambio deshecho: ').$step->label.'.');
        } catch (EditHistoryConflict $exception) {
            $this->flash($exception->getMessage());
        }
    }

    private function mutate(string $label, Closure $change): mixed
    {
        $result = app(EditHistoryService::class)->captureChange(
            auth()->user(),
            EditHistoryService::Evaluation,
            $this->evaluacion->id,
            $label,
            $change,
        );
        unset($this->historyState);

        return $result;
    }

    public function iniciarEliminarPregunta(int $preguntaId): void
    {
        $this->deletingPreguntaId = $preguntaId;
        $this->deletingType = 'pregunta';
    }

    public function iniciarEliminarOpcion(int $opcionId): void
    {
        $this->deletingOpcionId = $opcionId;
        $this->deletingType = 'opcion';
    }

    public function confirmarEliminarPregunta(): void
    {
        if ($this->deletingPreguntaId) {
            $this->eliminarPregunta($this->deletingPreguntaId);
            $this->cancelarEliminar();
        }
    }

    public function confirmarEliminarOpcion(): void
    {
        if ($this->deletingOpcionId) {
            $this->eliminarOpcion($this->deletingOpcionId);
            $this->cancelarEliminar();
        }
    }

    public function cancelarEliminar(): void
    {
        $this->deletingPreguntaId = null;
        $this->deletingOpcionId = null;
        $this->deletingType = '';
    }

    public function render()
    {
        return view('livewire.capacitador.editar-evaluacion');
    }
}
