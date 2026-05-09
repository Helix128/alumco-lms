<?php

namespace App\Livewire\Feedback;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

class PlatformFeedbackWidget extends Component
{
    public bool $open = false;

    public string $categoria = 'sugerencia';

    public string $mensaje = '';

    public string $estado = '';

    public function guardar(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        $data = $this->validate([
            'categoria' => ['required', Rule::in(['problema', 'sugerencia', 'accesibilidad', 'contenido'])],
            'mensaje' => ['required', 'string', 'min:6', 'max:1200'],
        ]);

        Feedback::create([
            'user_id' => $user->id,
            'tipo' => Feedback::TipoPlataforma,
            'categoria' => $data['categoria'],
            'mensaje' => $data['mensaje'],
            'estado' => Feedback::EstadoNuevo,
        ]);

        $this->reset(['mensaje']);
        $this->categoria = 'sugerencia';
        $this->estado = 'Feedback enviado al equipo de la plataforma.';
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.feedback.platform-feedback-widget');
    }
}
