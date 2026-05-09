<x-mail::message>
# Hola, {{ $name }}.

Se creó un nuevo ticket de soporte técnico.

<x-mail::panel>
**Ticket #{{ $ticket->id }}**  
Asunto: {{ $ticket->subject }}  
Solicitante: {{ $ticket->requesterName() }}
</x-mail::panel>

<x-mail::button :url="$ticketUrl" color="primary">
Revisar ticket
</x-mail::button>

@include('emails.components.signature')
</x-mail::message>
