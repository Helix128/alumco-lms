<?php

namespace App\Livewire\Feedback;

use App\Models\Curso;
use App\Models\Feedback;
use App\Models\User;
use App\Services\Cursos\CourseAccessService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CourseFeedbackForm extends Component
{
    public Curso $curso;

    public int $progreso = 0;

    public ?int $rating = null;

    public string $categoria = 'utilidad';

    public string $mensaje = '';

    public string $estado = '';

    public function mount(): void
    {
        $feedback = Feedback::query()
            ->where('user_id', auth()->id())
            ->where('curso_id', $this->curso->id)
            ->where('tipo', Feedback::TipoCurso)
            ->first();

        if ($feedback) {
            $this->rating = $feedback->rating;
            $this->categoria = $feedback->categoria;
            $this->mensaje = (string) $feedback->mensaje;
        }
    }

    public function guardar(CourseAccessService $courseAccessService): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User && $courseAccessService->canViewAsWorker($user, $this->curso), 403);
        abort_unless($this->progreso >= 100, 403);

        $data = $this->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'categoria' => ['required', Rule::in(['utilidad', 'claridad', 'contenido', 'duracion'])],
            'mensaje' => ['nullable', 'string', 'max:1200'],
        ]);

        Feedback::updateOrCreate(
            [
                'user_id' => $user->id,
                'curso_id' => $this->curso->id,
                'tipo' => Feedback::TipoCurso,
            ],
            [
                'categoria' => $data['categoria'],
                'rating' => $data['rating'],
                'mensaje' => $data['mensaje'] ?: null,
                'estado' => Feedback::EstadoNuevo,
            ],
        );

        $this->estado = 'Gracias. Tu feedback quedó registrado.';
        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.feedback.course-feedback-form');
    }
}
