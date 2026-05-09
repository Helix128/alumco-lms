<?php

namespace App\Livewire\Capacitador;

use App\Models\Curso;
use App\Models\Feedback;
use App\Models\User;
use Livewire\Component;

class CourseFeedbackSummary extends Component
{
    public Curso $curso;

    public function render()
    {
        abort_unless(auth()->user() instanceof User && auth()->user()->can('manage', $this->curso), 403);

        $feedbacks = Feedback::query()
            ->with('user')
            ->where('curso_id', $this->curso->id)
            ->where('tipo', Feedback::TipoCurso)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('livewire.capacitador.course-feedback-summary', [
            'feedbacks' => $feedbacks,
            'promedio' => $feedbacks->whereNotNull('rating')->avg('rating'),
        ]);
    }
}
