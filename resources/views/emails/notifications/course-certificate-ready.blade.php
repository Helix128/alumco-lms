<x-mail::message>
# Hola, {{ $name }}.

Completaste el curso **{{ $courseTitle }}** y tu certificado ya está disponible.

<x-mail::button :url="$downloadUrl" color="success">
Descargar certificado
</x-mail::button>

Por seguridad, debes iniciar sesión para descargarlo.

@include('emails.components.signature')
</x-mail::message>
