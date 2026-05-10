<x-mail::message>
# Hola, {{ $name }}.

@if($type === 'updated')
La planificación de la capacitación **{{ $courseTitle }}** fue actualizada.
@else
Tienes una nueva planificación para la capacitación **{{ $courseTitle }}**.
@endif

<x-mail::panel>
Inicio: **{{ $startDate }}**  
Término: **{{ $endDate }}**
</x-mail::panel>

<x-mail::button :url="$courseUrl" color="primary">
Ver capacitación
</x-mail::button>

@include('emails.components.signature')
</x-mail::message>
