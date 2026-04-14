@extends('layouts.panel')

@section('title', $curso->titulo)

@push('styles')
<style>
    .modulo-item { cursor: grab; }
    .modulo-item.dragging { opacity: 0.4; }
    .modulo-item.drag-over { border-top: 3px solid #205099; }
</style>
@endpush

@section('content')
    {{-- Header del curso --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('capacitador.cursos.index') }}" class="text-sm text-Alumco-blue hover:underline">
                ← Mis cursos
            </a>
            <h2 class="text-2xl font-bold text-Alumco-gray mt-1">{{ $curso->titulo }}</h2>
            @if ($curso->fecha_inicio)
                <p class="text-sm text-Alumco-gray/60">
                    {{ $curso->fecha_inicio->format('d/m/Y') }} — {{ $curso->fecha_fin->format('d/m/Y') }}
                </p>
            @endif
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('capacitador.cursos.editar', $curso) }}"
               class="border border-gray-300 text-Alumco-gray px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                Editar
            </a>
            <a href="{{ route('capacitador.cursos.participantes.index', $curso) }}"
               class="bg-Alumco-blue/10 text-Alumco-blue px-4 py-2 rounded-lg text-sm font-semibold hover:brightness-110 transition">
                Ver participantes
            </a>
        </div>
    </div>

    {{-- Asignación de estamentos (Interno o Admin) --}}
    @if (auth()->user()->isCapacitadorInterno() || auth()->user()->hasAdminAccess())
        <div class="filter-card mb-6">
            <h3 class="font-bold text-Alumco-gray mb-4">Asignación de estamentos</h3>
            @livewire('capacitador.gestion-estamentos', ['curso' => $curso])
        </div>
    @endif

    {{-- Módulos --}}
    <div class="filter-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-Alumco-gray">Módulos del curso</h3>
            <a href="{{ route('capacitador.cursos.modulos.crear', $curso) }}"
               class="bg-Alumco-green-vivid text-white px-4 py-2 rounded-lg text-sm font-semibold hover:brightness-110 transition">
                + Agregar módulo
            </a>
        </div>

        <div id="modulos-list">
            @forelse ($curso->modulos as $modulo)
                <div class="modulo-item flex items-center gap-3 border border-gray-200 rounded-xl p-3 mb-2 bg-white"
                     data-id="{{ $modulo->id }}" draggable="true">
                    {{-- Drag handle --}}
                    <svg class="w-5 h-5 text-gray-400 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6h2v2H8V6zm0 4h2v2H8v-2zm0 4h2v2H8v-2zm6-8h2v2h-2V6zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
                    </svg>

                    {{-- Número e ícono tipo --}}
                    <span class="bg-Alumco-blue text-white rounded-full w-7 h-7 flex items-center justify-center
                                 text-xs font-black shrink-0">{{ $modulo->orden }}</span>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-Alumco-gray text-sm truncate">{{ $modulo->titulo }}</p>
                        <p class="text-xs text-Alumco-gray/50">
                            {{ \App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido }}
                            @if ($modulo->duracion_minutos)
                                · {{ $modulo->duracion_minutos }} min
                            @endif
                        </p>
                    </div>

                    {{-- Acciones --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('capacitador.cursos.modulos.editar', [$curso, $modulo]) }}"
                           class="text-xs text-Alumco-blue hover:underline font-semibold">Propiedades</a>
                        @if ($modulo->tipo_contenido === 'evaluacion' && $modulo->evaluacion)
                            <a href="{{ route('capacitador.cursos.modulos.evaluacion', [$curso, $modulo]) }}"
                               class="text-xs text-Alumco-green-vivid hover:underline font-semibold">Evaluación</a>
                        @endif
                        <form action="{{ route('capacitador.cursos.modulos.destroy', [$curso, $modulo]) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar este módulo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 font-semibold">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-center text-Alumco-gray/40 py-8">
                    No hay módulos. <a href="{{ route('capacitador.cursos.modulos.crear', $curso) }}"
                    class="text-Alumco-blue hover:underline">Agrega el primero.</a>
                </p>
            @endforelse
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const list = document.getElementById('modulos-list');
    if (!list) return;

    let draggingEl = null;

    list.addEventListener('dragstart', e => {
        draggingEl = e.target.closest('.modulo-item');
        if (!draggingEl) return;
        draggingEl.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    list.addEventListener('dragend', () => {
        if (draggingEl) draggingEl.classList.remove('dragging');
        list.querySelectorAll('.modulo-item').forEach(el => el.classList.remove('drag-over'));
        draggingEl = null;
        guardarOrden();
    });

    list.addEventListener('dragover', e => {
        e.preventDefault();
        const target = e.target.closest('.modulo-item');
        if (!target || target === draggingEl) return;
        list.querySelectorAll('.modulo-item').forEach(el => el.classList.remove('drag-over'));
        target.classList.add('drag-over');

        const bounding = target.getBoundingClientRect();
        const offset = e.clientY - bounding.top;
        if (offset > bounding.height / 2) {
            target.parentNode.insertBefore(draggingEl, target.nextSibling);
        } else {
            target.parentNode.insertBefore(draggingEl, target);
        }
    });

    function guardarOrden() {
        const ids = [...list.querySelectorAll('.modulo-item')].map(el => parseInt(el.dataset.id));
        fetch('{{ route('capacitador.cursos.modulos.reordenar', $curso) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ orden: ids })
        });
    }
})();
</script>
@endpush
