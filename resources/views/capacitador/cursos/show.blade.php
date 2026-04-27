@extends('layouts.panel')

@section('title', $curso->titulo)

@section('header_title', 'Visor de Curso')

@push('styles')
<style>
    .modulo-item { cursor: grab; }
    .modulo-item.dragging { opacity: 0.4; transform: scale(0.98); }
    .modulo-item.drag-over { border-top: 3px solid #205099; }
</style>
@endpush

@section('content')
    <div class="space-y-8">
        
        {{-- Zona Superior: Cabecera y Acciones --}}
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="space-y-4">
                <a href="{{ route('capacitador.cursos.index') }}" 
                   class="inline-flex items-center gap-2 text-xs font-black uppercase tracking-widest text-Alumco-blue hover:text-Alumco-blue/70 transition-colors group">
                    <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Mis cursos
                </a>
                
                <div>
                    <h2 class="text-4xl font-display font-black text-Alumco-blue leading-tight tracking-tight max-w-4xl">
                        {{ $curso->titulo }}
                    </h2>
                    
                    <div class="flex flex-wrap items-center gap-4 mt-4">

                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                {{-- Botón Duplicar --}}
                <button type="button" 
                        onclick="openDuplicateModal('{{ route('capacitador.cursos.duplicar', $curso) }}', '{{ addslashes($curso->titulo) }}')" 
                        class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 bg-white border border-gray-100 text-Alumco-gray font-display font-black text-[11px] uppercase tracking-widest py-4 px-6 rounded-2xl shadow-sm hover:shadow-md hover:border-Alumco-blue/20 transition-all active:scale-95"
                        title="Crear una nueva versión de este curso">
                    <svg class="w-5 h-5 text-Alumco-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                    </svg>
                    Duplicar
                </button>

                <a href="{{ route('capacitador.cursos.participantes.index', $curso) }}"
                   class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 bg-white border border-gray-100 text-Alumco-blue font-display font-black text-[11px] uppercase tracking-widest py-4 px-8 rounded-2xl shadow-sm hover:shadow-md hover:border-Alumco-blue/20 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Participantes
                </a>
                <a href="{{ route('capacitador.cursos.editar', $curso) }}"
                   class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 bg-Alumco-blue text-white font-display font-black text-[11px] uppercase tracking-widest py-4 px-8 rounded-2xl shadow-lg shadow-Alumco-blue/20 hover:brightness-110 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Editar curso
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
            
            {{-- Columna Izquierda: Módulos --}}
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between bg-gray-50/30">
                        <div>
                            <h3 class="text-sm font-black text-Alumco-blue uppercase tracking-[0.2em]">Módulos del programa</h3>
                            <p class="text-[10px] text-Alumco-gray/40 font-bold uppercase mt-1">Arrastra para reordenar el contenido</p>
                        </div>
                        <a href="{{ route('capacitador.cursos.modulos.crear', $curso) }}"
                           class="inline-flex items-center gap-2 bg-Alumco-green-vivid text-white font-display font-black text-[10px] uppercase tracking-widest py-2.5 px-6 rounded-xl shadow-lg shadow-Alumco-green-vivid/20 hover:brightness-105 transition-all active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nuevo Módulo
                        </a>
                    </div>

                    <div id="modulos-list" class="p-4 space-y-3">
                        @forelse ($curso->modulos as $modulo)
                            <div class="modulo-item flex items-center gap-4 bg-white border border-gray-100 rounded-2xl p-4 hover:border-Alumco-blue/30 hover:shadow-md transition-all group"
                                 data-id="{{ $modulo->id }}" draggable="true">
                                
                                {{-- Drag handle --}}
                                <div class="cursor-grab active:cursor-grabbing p-2 text-gray-200 group-hover:text-Alumco-blue/20 transition-colors">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 6h2v2H8V6zm0 4h2v2H8v-2zm0 4h2v2H8v-2zm6-8h2v2h-2V6zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
                                    </svg>
                                </div>

                                {{-- Orden e Icono --}}
                                <div class="w-10 h-10 rounded-xl bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center font-display font-black text-xs shrink-0 border border-Alumco-blue/10">
                                    {{ $modulo->orden }}
                                </div>

                                {{-- Info --}}
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-Alumco-gray text-sm truncate group-hover:text-Alumco-blue transition-colors">
                                        {{ $modulo->titulo }}
                                    </h4>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-[10px] font-black uppercase tracking-widest text-Alumco-gray/30 bg-gray-50 px-2 py-0.5 rounded-md">
                                            {{ \App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido }}
                                        </span>
                                        @if ($modulo->duracion_minutos)
                                            <span class="text-[10px] font-bold text-Alumco-gray/40 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-12 0 9 9 0 0112 0z"></path></svg>
                                                {{ $modulo->duracion_minutos }} min
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Acciones --}}
                                <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('capacitador.cursos.modulos.editar', [$curso, $modulo]) }}"
                                       class="p-2 text-Alumco-gray/40 hover:text-Alumco-blue hover:bg-Alumco-blue/5 rounded-lg transition-all" title="Propiedades">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </a>
                                    @if ($modulo->tipo_contenido === 'evaluacion' && $modulo->evaluacion)
                                        <a href="{{ route('capacitador.cursos.modulos.evaluacion', [$curso, $modulo]) }}"
                                           class="p-2 text-Alumco-gray/40 hover:text-Alumco-green-vivid hover:bg-Alumco-green-vivid/5 rounded-lg transition-all" title="Gestionar Evaluación">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </a>
                                    @endif
                                    <form action="{{ route('capacitador.cursos.modulos.destroy', [$curso, $modulo]) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar este módulo?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-Alumco-gray/40 hover:text-Alumco-coral hover:bg-Alumco-coral/5 rounded-lg transition-all" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-20 text-center space-y-4">
                                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto text-gray-200">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-Alumco-gray/30 uppercase tracking-widest">No hay módulos creados aún</p>
                                <a href="{{ route('capacitador.cursos.modulos.crear', $curso) }}"
                                   class="inline-block text-sm font-black text-Alumco-blue hover:underline">Comenzar el programa académico</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Columna Derecha: Estamentos y Configuración --}}
            <div class="space-y-6">
                @if (auth()->user()->isCapacitadorInterno() || auth()->user()->hasAdminAccess())
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <div>
                            <h3 class="text-sm font-black text-Alumco-blue uppercase tracking-[0.2em]">Audiencia Objetivo</h3>
                            <p class="text-[10px] text-Alumco-gray/40 font-bold uppercase mt-1">Gestión de acceso por estamentos</p>
                        </div>
                        
                        @livewire('capacitador.gestion-estamentos', ['curso' => $curso])
                    </div>
                @endif


            </div>
        </div>
    </div>

    <!-- Modal Duplicar Curso -->
    <div id="duplicate-modal-backdrop" class="fixed inset-0 bg-Alumco-gray/40 backdrop-blur-sm z-50 opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true"></div>
    <div id="duplicate-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 scale-95" aria-hidden="true">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden">
            <form id="duplicate-form" method="POST" action="">
                @csrf
                <div class="p-8">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-12 h-12 rounded-full bg-Alumco-blue/10 text-Alumco-blue flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-display font-black text-Alumco-blue">Duplicar Curso</h3>
                            <p class="text-[10px] font-bold text-Alumco-gray/40 uppercase tracking-widest mt-1">Crear nueva versión</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-black text-Alumco-blue/40 uppercase tracking-widest">Título de la nueva versión</label>
                        <input type="text" id="duplicate-title" name="titulo" required
                               class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">
                    </div>
                </div>

                <div class="p-6 border-t border-gray-50 bg-gray-50/30 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeDuplicateModal()" class="px-6 py-2.5 text-sm font-bold text-Alumco-gray/50 hover:text-Alumco-coral transition-colors text-center">Cancelar</button>
                    <button type="submit" class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-black text-xs uppercase tracking-[0.2em] py-4 px-8 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95 flex items-center gap-2">
                        Crear copia
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const dupBackdrop = document.getElementById('duplicate-modal-backdrop');
    const dupModal = document.getElementById('duplicate-modal');
    const dupForm = document.getElementById('duplicate-form');
    const dupTitle = document.getElementById('duplicate-title');
    let isDupOpen = false;

    function openDuplicateModal(actionUrl, tituloActual) {
        dupForm.action = actionUrl;
        dupTitle.value = tituloActual + ' (Copia)';

        dupBackdrop.classList.remove('opacity-0', 'pointer-events-none');
        dupModal.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
        dupModal.classList.add('scale-100');
        dupTitle.focus();
        isDupOpen = true;
    }

    function closeDuplicateModal() {
        dupBackdrop.classList.add('opacity-0', 'pointer-events-none');
        dupModal.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
        dupModal.classList.remove('scale-100');
        isDupOpen = false;
    }

    dupBackdrop.addEventListener('click', closeDuplicateModal);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isDupOpen) closeDuplicateModal(); });

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
