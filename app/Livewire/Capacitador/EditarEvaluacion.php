<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\Evaluacion;
use App\Models\Opcion;
use App\Models\Pregunta;
use Livewire\Component;

class EditarEvaluacion extends Component
{
    public Evaluacion $evaluacion;
    public Curso $curso;

    public array $preguntas = [];
    public string $nuevaPreguntaEnunciado = '';
    public string $flashMensaje = '';

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
            ->map(fn($p) => [
                'id'       => $p->id,
                'enunciado' => $p->enunciado,
                'orden'    => $p->orden,
                'opciones' => $p->opciones->map(fn($o) => [
                    'id'         => $o->id,
                    'texto'      => $o->texto,
                    'es_correcta' => (bool) $o->es_correcta,
                    'orden'      => $o->orden,
                ])->values()->toArray(),
            ])
            ->values()
            ->toArray();
    }

    public function agregarPregunta(): void
    {
        $this->validate(['nuevaPreguntaEnunciado' => 'required|string|min:3']);

        $pregunta = Pregunta::create([
            'evaluacion_id' => $this->evaluacion->id,
            'enunciado'     => $this->nuevaPreguntaEnunciado,
            'orden'         => count($this->preguntas) + 1,
        ]);

        $this->preguntas[] = [
            'id'        => $pregunta->id,
            'enunciado' => $pregunta->enunciado,
            'orden'     => $pregunta->orden,
            'opciones'  => [],
        ];

        $this->nuevaPreguntaEnunciado = '';
    }

    public function eliminarPregunta(int $preguntaId): void
    {
        Pregunta::destroy($preguntaId);

        $this->preguntas = array_values(
            array_filter($this->preguntas, fn($p) => $p['id'] !== $preguntaId)
        );

        // Reindexar orden
        foreach ($this->preguntas as $i => &$p) {
            $p['orden'] = $i + 1;
        }
    }

    public function agregarOpcion(int $preguntaId): void
    {
        $opcion = Opcion::create([
            'pregunta_id' => $preguntaId,
            'texto'       => '',
            'es_correcta' => false,
            'orden'       => 1,
        ]);

        foreach ($this->preguntas as &$p) {
            if ($p['id'] === $preguntaId) {
                $p['opciones'][] = [
                    'id'         => $opcion->id,
                    'texto'      => '',
                    'es_correcta' => false,
                    'orden'      => count($p['opciones']) + 1,
                ];
                break;
            }
        }
    }

    public function eliminarOpcion(int $opcionId): void
    {
        Opcion::destroy($opcionId);

        foreach ($this->preguntas as &$p) {
            $p['opciones'] = array_values(
                array_filter($p['opciones'], fn($o) => $o['id'] !== $opcionId)
            );
        }
    }

    public function toggleCorrecta(int $opcionId): void
    {
        // Buscar a qué pregunta pertenece la opción
        foreach ($this->preguntas as &$p) {
            foreach ($p['opciones'] as $o) {
                if ($o['id'] === $opcionId) {
                    // Poner todas las opciones de esta pregunta en false
                    foreach ($p['opciones'] as &$op) {
                        $op['es_correcta'] = ($op['id'] === $opcionId);
                        Opcion::where('id', $op['id'])->update(['es_correcta' => $op['es_correcta']]);
                    }
                    break 2;
                }
            }
        }
    }

    public function guardarTextos(): void
    {
        foreach ($this->preguntas as $p) {
            Pregunta::where('id', $p['id'])->update([
                'enunciado' => $p['enunciado'],
                'orden'     => $p['orden'],
            ]);

            foreach ($p['opciones'] as $o) {
                Opcion::where('id', $o['id'])->update([
                    'texto'      => $o['texto'],
                    'es_correcta' => $o['es_correcta'],
                    'orden'      => $o['orden'],
                ]);
            }
        }

        $this->flashMensaje = 'Cambios guardados correctamente.';
        $this->dispatch('evaluacion-guardada');
    }

    public function render()
    {
        return view('livewire.capacitador.editar-evaluacion');
    }
}
