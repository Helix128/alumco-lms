<x-mail::message>
# Hola, {{ $name }}.

El curso **{{ $courseTitle }}** ya está disponible para ti.

<x-mail::button :url="$courseUrl" color="primary">
Ingresar al curso
</x-mail::button>

Estará habilitado hasta el **{{ $availableUntil }}**.

@include('emails.components.signature')
</x-mail::message>
