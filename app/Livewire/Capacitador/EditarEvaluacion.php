<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\GlobalSetting;
use App\Models\Opcion;
use App\Models\Pregunta;
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
    }

    public function eliminarPregunta(int $preguntaId): void
    {
        Pregunta::destroy($preguntaId);

        $this->preguntas = array_values(
            array_filter($this->preguntas, fn ($p) => $p['id'] !== $preguntaId)
        );

        foreach ($this->preguntas as $i => &$p) {
            $p['orden'] = $i + 1;
        }
    }

    public function agregarOpcion(int $preguntaId): void
    {
        $newOrden = 1;
        foreach ($this->preguntas as $p) {
            if ($p['id'] === $preguntaId) {
                $newOrden = count($p['opciones']) + 1;
                break;
            }
        }

        $opcion = Opcion::create([
            'pregunta_id' => $preguntaId,
            'texto' => '',
            'es_correcta' => false,
            'orden' => $newOrden,
        ]);

        foreach ($this->preguntas as &$p) {
            if ($p['id'] === $preguntaId) {
                $p['opciones'][] = [
                    'id' => $opcion->id,
                    'texto' => '',
                    'es_correcta' => false,
                    'orden' => $newOrden,
                ];
                break;
            }
        }

        $this->dispatch('opcion-agregada', preguntaId: $preguntaId);
    }

    public function eliminarOpcion(int $opcionId): void
    {
        Opcion::destroy($opcionId);

        foreach ($this->preguntas as &$p) {
            $p['opciones'] = array_values(
                array_filter($p['opciones'], fn ($o) => $o['id'] !== $opcionId)
            );
        }
    }

    public function toggleCorrecta(int $opcionId): void
    {
        foreach ($this->preguntas as &$p) {
            foreach ($p['opciones'] as $o) {
                if ($o['id'] === $opcionId) {
                    foreach ($p['opciones'] as &$op) {
                        $op['es_correcta'] = ($op['id'] === $opcionId);
                        Opcion::where('id', $op['id'])->update(['es_correcta' => $op['es_correcta']]);
                    }
                    break 2;
                }
            }
        }
    }

    public function guardarEnunciado(int $preguntaId): void
    {
        $index = $this->indexFor($preguntaId);
        $this->validate(["preguntas.{$index}.enunciado" => 'required|string|min:3']);

        Pregunta::where('id', $preguntaId)->update([
            'enunciado' => $this->preguntas[$index]['enunciado'],
        ]);

        $this->flash('Pregunta guardada.');
    }

    public function guardarTextoOpcion(int $opcionId): void
    {
        foreach ($this->preguntas as $p) {
            foreach ($p['opciones'] as $o) {
                if ($o['id'] === $opcionId) {
                    Opcion::where('id', $opcionId)->update(['texto' => $o['texto']]);

                    return;
                }
            }
        }
    }

    public function reordenarPreguntas(array $orden): void
    {
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
            ->map(function ($p, $i) {
                $p['orden'] = $i + 1;

                return $p;
            })
            ->toArray();
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
