<x-mail::message>
# Hola, {{ $name }}.

Se creó una cuenta para ti en **Alumco LMS**.

<x-mail::panel>
Para activar tu acceso, configura tu contraseña usando el siguiente botón.
</x-mail::panel>

<x-mail::button :url="$setupUrl" color="primary">
Configurar contraseña
</x-mail::button>

Este enlace expira en **{{ $expiresInMinutes }} minutos**.

Si no reconoces este registro, puedes ignorar este mensaje.

@include('emails.components.signature')
</x-mail::message>
