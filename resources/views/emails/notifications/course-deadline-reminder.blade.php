<x-mail::message>
# Hola, {{ $name }}.

Tu capacitación **{{ $courseTitle }}** vence el **{{ $deadlineDate }}**.

Llevas un avance de **{{ $progress }}%** y aún tienes módulos pendientes.

<x-mail::button :url="$courseUrl" color="primary">
Continuar capacitación
</x-mail::button>

@include('emails.components.signature')
</x-mail::message>
