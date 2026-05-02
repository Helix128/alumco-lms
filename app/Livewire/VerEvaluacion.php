<?php

namespace App\Livewire;

use App\Models\Curso;
use App\Models\IntentoEvaluacion;
use App\Models\Modulo;
use App\Models\ProgresoModulo;
use App\Models\RespuestaEvaluacion;
use App\Services\CertificadoService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class VerEvaluacion extends Component
{
    public Modulo $modulo;

    public Curso $curso;

    public Collection $preguntas;

    public int $indiceActual = 0;

    public array $respuestasSeleccionadas = []; // [pregunta_id => opcion_id]

    public bool $finalizada = false;

    public int $puntaje = 0;

    public bool $aprobado = false;

    public bool $bloqueada = false;

    public int $intentosRestantes = 2;

    public bool $certificadoGenerado = false;

    public function mount(Modulo $modulo, Curso $curso): void
    {
        $this->modulo = $modulo;
        $this->curso = $curso;

        if (! $modulo->evaluacion) {
            $this->bloqueada = true;
            $this->intentosRestantes = 0;
            $this->preguntas = collect();

            return;
        }

        $this->preguntas = $modulo->evaluacion
            ->preguntas()
            ->with('opciones')
            ->get();

        // Gate de intentos semanales
        $intentosEstaSemana = IntentoEvaluacion::where('user_id', auth()->id())
            ->where('evaluacion_id', $modulo->evaluacion->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $max = $modulo->evaluacion->max_intentos_semanales ?? 2;
        $this->bloqueada = $intentosEstaSemana >= $max;
        $this->intentosRestantes = max(0, $max - $intentosEstaSemana);
    }

    public function seleccionarOpcion(int $preguntaId, int $opcionId): void
    {
        $this->respuestasSeleccionadas[$preguntaId] = $opcionId;
    }

    public function siguiente(): void
    {
        $preguntaId = $this->preguntas[$this->indiceActual]->id;

        if (! isset($this->respuestasSeleccionadas[$preguntaId])) {
            return;
        }

        if ($this->indiceActual < $this->preguntas->count() - 1) {
            $this->indiceActual++;
        } else {
            $this->finalizar();
        }
    }

    public function anterior(): void
    {
        if ($this->indiceActual > 0) {
            $this->indiceActual--;
        }
    }

    public function finalizar(): void
    {
        if ($this->bloqueada) {
            return;
        }

        $puntaje = 0;

        foreach ($this->preguntas as $pregunta) {
            $opcionId = $this->respuestasSeleccionadas[$pregunta->id] ?? null;
            if ($opcionId && $pregunta->opciones->find($opcionId)?->es_correcta) {
                $puntaje++;
            }
        }

        $total = $this->preguntas->count();
        $aprobado = $puntaje >= $this->modulo->evaluacion->puntos_aprobacion;

        // Persistir el intento
        $intento = IntentoEvaluacion::create([
            'user_id' => auth()->id(),
            'evaluacion_id' => $this->modulo->evaluacion->id,
            'puntaje' => $puntaje,
            'total_preguntas' => $total,
            'aprobado' => $aprobado,
        ]);

        foreach ($this->respuestasSeleccionadas as $preguntaId => $opcionId) {
            RespuestaEvaluacion::create([
                'intento_id' => $intento->id,
                'pregunta_id' => $preguntaId,
                'opcion_id' => $opcionId,
            ]);
        }

        // Marcar módulo como completado si aprobó
        if ($aprobado) {
            ProgresoModulo::updateOrCreate(
                ['user_id' => auth()->id(), 'modulo_id' => $this->modulo->id],
                ['completado' => true, 'fecha_completado' => now()]
            );

            // Auto-generar certificado si el curso está 100% completo
            $this->curso->load(['modulos.progresos' => fn ($q) => $q->where('user_id', auth()->id())]);
            if ($this->curso->progresoParaUsuario(auth()->user()) === 100) {
                try {
                    app(CertificadoService::class)->generarParaUsuario(auth()->user(), $this->curso);
                    $this->certificadoGenerado = true;
                } catch (\Throwable) {
                    // No bloquear al usuario si la generación falla
                }
            }
        }

        $this->puntaje = $puntaje;
        $this->aprobado = $aprobado;
        $this->finalizada = true;
    }

    public function render()
    {
        return view('livewire.ver-evaluacion', [
            'preguntaActual' => $this->finalizada
                ? null
                : $this->preguntas[$this->indiceActual],
        ]);
    }
}
