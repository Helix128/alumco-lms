<x-mail::message>
# Hola, {{ $name }}.

La capacitación **{{ $courseTitle }}** ya está disponible para ti.

<x-mail::button :url="$courseUrl" color="primary">
Ingresar a la capacitación
</x-mail::button>

Estará habilitada hasta el **{{ $availableUntil }}**.

@include('emails.components.signature')
</x-mail::message>
