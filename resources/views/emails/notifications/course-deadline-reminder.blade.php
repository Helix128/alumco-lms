<x-mail::message>
# Hola, {{ $name }}.

Tu curso **{{ $courseTitle }}** vence el **{{ $deadlineDate }}**.

Llevas un avance de **{{ $progress }}%** y aún tienes módulos pendientes.

<x-mail::button :url="$courseUrl" color="primary">
Continuar curso
</x-mail::button>

@include('emails.components.signature')
</x-mail::message>
