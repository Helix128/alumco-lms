<?php

namespace App\Livewire\Support;

use App\Actions\Support\CreateSupportTicketAction;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateTicket extends Component
{
    use WithFileUploads;

    public string $contact_name = '';

    public string $contact_email = '';

    public string $category = SupportTicket::CategoryPlatformError;

    public string $subject = '';

    public string $description = '';

    /**
     * @var array<int, mixed>
     */
    public array $attachments = [];

    public string $statusMessage = '';

    public bool $embedded = false;

    public function mount(bool $embedded = false): void
    {
        $this->embedded = $embedded;

        if (auth()->check()) {
            $this->contact_name = auth()->user()->name;
            $this->contact_email = auth()->user()->email;
        }
    }

    public function submit(CreateSupportTicketAction $action): void
    {
        $this->ensureRateLimitIsClear();

        $user = auth()->user();
        $isGuest = ! $user instanceof User;

        $data = $this->validate([
            'contact_name' => [$isGuest ? 'required' : 'nullable', 'string', 'min:3', 'max:120'],
            'contact_email' => [$isGuest ? 'required' : 'nullable', 'email:rfc', 'max:160'],
            'category' => ['required', Rule::in(SupportTicket::Categories)],
            'subject' => ['required', 'string', 'min:6', 'max:160'],
            'description' => ['required', 'string', 'min:12', 'max:5000'],
            'attachments' => ['array', 'max:3'],
            'attachments.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'contact_name.required' => 'Indica tu nombre para que podamos contactarte.',
            'contact_email.required' => 'Indica un correo válido para responder tu solicitud.',
            'contact_email.email' => 'El correo de contacto debe ser válido.',
            'subject.required' => 'Indica el asunto del ticket.',
            'subject.min' => 'El asunto debe tener al menos 6 caracteres.',
            'description.required' => 'Describe el problema para poder revisarlo.',
            'description.min' => 'La descripción debe tener al menos 12 caracteres.',
            'attachments.max' => 'Puedes adjuntar hasta 3 capturas.',
            'attachments.*.mimes' => 'Las capturas deben ser jpg, jpeg, png o webp.',
            'attachments.*.max' => 'Cada captura puede pesar hasta 4 MB.',
        ]);

        $ticket = $action->handle([
            'contact_name' => $data['contact_name'] ?: null,
            'contact_email' => $data['contact_email'] ?: null,
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'technical_context' => $this->technicalContext(),
        ], $user instanceof User ? $user : null, $data['attachments'] ?? []);

        $this->reset(['subject', 'description', 'attachments']);
        $this->category = SupportTicket::CategoryPlatformError;
        $this->statusMessage = 'Ticket #'.$ticket->id.' enviado. El equipo técnico lo revisará.';
        $this->dispatch('support-ticket-created');
    }

    /**
     * @return array<string, mixed>
     */
    private function technicalContext(): array
    {
        return [
            'url' => request()->headers->get('referer') ?: request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'authenticated' => auth()->check(),
        ];
    }

    private function ensureRateLimitIsClear(): void
    {
        $key = 'support-ticket:'.(auth()->id() ?: request()->ip()).':'.strtolower($this->contact_email);

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'contact_email' => 'Espera '.$seconds.' segundos antes de enviar otro ticket.',
            ]);
        }

        RateLimiter::hit($key, 3600);
    }

    public function render()
    {
        return view('livewire.support.create-ticket', [
            'categories' => SupportTicket::Categories,
        ]);
    }
}
