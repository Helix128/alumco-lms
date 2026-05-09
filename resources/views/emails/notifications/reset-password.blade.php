<x-mail::message>
# Hola, {{ $name }}.

Recibimos una solicitud para restablecer tu contraseña en **Alumco LMS**.

<x-mail::button :url="$resetUrl" color="primary">
Restablecer contraseña
</x-mail::button>

El enlace expira en **{{ $expiresInMinutes }} minutos**.

Si no solicitaste este cambio, puedes ignorar este correo.

@include('emails.components.signature')
</x-mail::message>
