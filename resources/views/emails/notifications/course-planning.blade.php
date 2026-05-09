<x-mail::message>
# Hola, {{ $name }}.

@if($type === 'updated')
La planificación del curso **{{ $courseTitle }}** fue actualizada.
@else
Tienes una nueva planificación para el curso **{{ $courseTitle }}**.
@endif

<x-mail::panel>
Inicio: **{{ $startDate }}**  
Término: **{{ $endDate }}**
</x-mail::panel>

<x-mail::button :url="$courseUrl" color="primary">
Ver curso
</x-mail::button>

@include('emails.components.signature')
</x-mail::message>
