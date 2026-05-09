<x-mail::message>
# Hola, {{ $ticket->requesterName() }}.

@switch($type)
    @case('created')
Registramos tu solicitud de soporte correctamente.

**Ticket #{{ $ticket->id }}**: {{ $ticket->subject }}
        @break

    @case('resolved')
Tu ticket fue marcado como **resuelto** por el equipo técnico.

**Ticket #{{ $ticket->id }}**: {{ $ticket->subject }}
        @break

    @case('waiting_user')
Tu ticket requiere una acción de tu parte para continuar la revisión.

**Ticket #{{ $ticket->id }}**: {{ $ticket->subject }}
        @break

    @default
Recibiste una nueva respuesta del equipo técnico.

**Ticket #{{ $ticket->id }}**: {{ $ticket->subject }}
@endswitch

@if($ticketUrl)
<x-mail::button :url="$ticketUrl" color="primary">
Ver ticket
</x-mail::button>
@endif

@include('emails.components.signature')
</x-mail::message>
