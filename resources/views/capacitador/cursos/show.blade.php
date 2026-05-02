@extends('layouts.panel')

@section('title', $curso->titulo)

@section('header_title', 'Visor de Curso')

@push('styles')
<style>
    .seccion-item { transition: all 0.2s; border: 2px solid transparent; }
    .seccion-item.drag-over-section { border-color: var(--color-Alumco-blue); background-color: rgba(32, 80, 153, 0.02); }
    .modulos-list { transition: background-color 0.2s; border-radius: 1rem; padding-bottom: 1rem !important; }
    .modulos-list.drag-over-list { background-color: rgba(32, 80, 153, 0.04); min-height: 80px; }
    .modulo-item { cursor: grab; transition: transform 0.15s ease; }
    .modulo-item.dragging { opacity: 0; position: absolute; z-index: -1; }
    
    .modulo-placeholder {
        height: 76px;
        background-color: rgba(32, 80, 153, 0.06);
        border: 2px dashed rgba(32, 80, 153, 0.3);
        border-radius: 1rem;
        margin-bottom: 0.75rem;
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    .seccion-placeholder {
        height: 120px;
        background-color: rgba(32, 80, 153, 0.04);
        border: 2px dashed rgba(32, 80, 153, 0.2);
        border-radius: 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Zonas de captura entre secciones (invisibles hasta que reciben el placeholder) */
    .inter-section-dropzone {
        min-height: 32px;
        margin: -16px 0;
        position: relative;
        z-index: 20;
    }

    /* El placeholder se transforma al entrar en una zona inter-sección */
    .inter-section-dropzone .modulo-placeholder {
        margin: 16px 0; /* Empuja el contenido para hacer espacio real */
        height: 80px;
        background: rgba(175, 221, 131, 0.08);
        border: 2px dashed rgba(175, 221, 131, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: none; /* Desactivamos el pulso para mayor estabilidad */
    }

    .inter-section-dropzone .modulo-placeholder::after {
        content: "+ Soltar aquí para crear sección";
        font-family: var(--font-display);
        font-weight: 900;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--color-Alumco-green-accessible);
    }

    /* Guía de slot vacío */
    .empty-slot-guide {
        display: none;
        padding: 2rem 0;
        text-align: center;
        border: 2px dashed rgba(74, 74, 74, 0.4);
        border-radius: 1rem;
        pointer-events: none;
    }

    .modulos-list:not(:has(.modulo-item)):not(:has(.modulo-placeholder)) .empty-slot-guide {
        display: block !important;
    }

    .is-dragging-module .modulos-list:not(:has(.modulo-item)) .empty-slot-guide {
        background-color: rgba(32, 80, 153, 0.04);
        border-color: rgba(32, 80, 153, 0.4);
    }

    .is-dragging-module .modulos-list:not(:has(.modulo-item)) .empty-slot-guide p {
        color: var(--color-Alumco-blue);
        opacity: 0.8;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .7; }
    }
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
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" 
                        onclick="openDuplicateModal('{{ route('capacitador.cursos.duplicar', $curso) }}', '{{ addslashes($curso->titulo) }}')" 
                        class="flex-1 lg:flex-none inline-flex items-center justify-center gap-2 bg-white border border-gray-100 text-Alumco-gray font-display font-black text-[11px] uppercase tracking-widest py-4 px-6 rounded-2xl shadow-sm hover:shadow-md hover:border-Alumco-blue/20 transition-all active:scale-95">
                    <svg class="w-5 h-5 text-Alumco-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
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
            
            {{-- Columna Izquierda: Secciones y Módulos --}}
            <div class="xl:col-span-2 space-y-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-display font-black text-Alumco-blue">Estructura Académica</h3>
                        <p class="text-xs font-bold text-Alumco-gray/65 uppercase tracking-widest mt-1">Organiza el contenido en etapas</p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="openSeccionModal()"
                                class="inline-flex items-center gap-2 bg-white border border-Alumco-blue/20 text-Alumco-blue font-display font-black text-[10px] uppercase tracking-widest py-2.5 px-6 rounded-xl shadow-sm hover:bg-Alumco-blue/5 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nueva Sección
                        </button>
                        <a href="{{ route('capacitador.cursos.modulos.crear', $curso) }}"
                           class="inline-flex items-center gap-2 bg-Alumco-green-vivid text-white font-display font-black text-[10px] uppercase tracking-widest py-2.5 px-6 rounded-xl shadow-lg shadow-Alumco-green-vivid/20 hover:brightness-105 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Nuevo Módulo
                        </a>
                    </div>
                </div>

                <div id="secciones-container" class="space-y-6">
                    <div class="inter-section-dropzone" data-seccion-id="new_first"></div>
                    @foreach ($curso->secciones as $seccion)
                        <div class="seccion-item bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden" 
                             data-seccion-id="{{ $seccion->id }}">
                            <div class="px-8 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                                <div class="flex items-center gap-4">
                                    <div class="cursor-grab active:cursor-grabbing text-gray-300 hover:text-Alumco-blue transition-colors seccion-handle">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6h2v2H8V6zm0 4h2v2H8v-2zm0 4h2v2H8v-2zm6-8h2v2h-2V6zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z"/></svg>
                                    </div>
                                    <h4 class="font-display font-black text-Alumco-blue text-base uppercase tracking-wider">{{ $seccion->titulo }}</h4>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button onclick="openSeccionModal({{ $seccion->id }}, '{{ addslashes($seccion->titulo) }}')" class="p-2 text-gray-400 hover:text-Alumco-blue transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form action="{{ route('capacitador.cursos.secciones.destroy', [$curso, $seccion]) }}" method="POST" onsubmit="return confirm('¿Eliminar esta sección? Los módulos no se borrarán.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-Alumco-coral transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="p-4 space-y-3 modulos-list min-h-[50px]" data-seccion-id="{{ $seccion->id }}">
                                @foreach ($seccion->modulos as $modulo)
                                    @include('capacitador.cursos.partials.modulo-item', ['modulo' => $modulo, 'curso' => $curso])
                                @endforeach
                                <div class="empty-slot-guide">
                                    <p class="text-[10px] font-black uppercase tracking-widest text-Alumco-gray/65">Esta sección no tiene módulos aún</p>
                                </div>
                            </div>
                        </div>
                        <div class="inter-section-dropzone" data-seccion-id="new_{{ $loop->index }}"></div>
                    @endforeach

                    {{-- Módulos sin sección --}}
                    @if($curso->modulos->whereNull('seccion_id')->isNotEmpty())
                    <div class="seccion-item bg-gray-50/30 rounded-3xl border-2 border-dashed border-gray-200 overflow-hidden" data-seccion-id="null">
                        <div class="px-8 py-4 border-b border-gray-100 flex items-center justify-between">
                            <h4 class="font-display font-black text-Alumco-gray/65 text-xs uppercase tracking-[0.2em]">Módulos sin asignar</h4>
                        </div>
                        <div class="p-4 space-y-3 modulos-list min-h-[80px]" data-seccion-id="null">
                            @foreach ($curso->modulos->whereNull('seccion_id') as $modulo)
                                @include('capacitador.cursos.partials.modulo-item', ['modulo' => $modulo, 'curso' => $curso])
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Columna Derecha: Audiencia --}}
            <div class="space-y-6">
                @if (auth()->user()->isCapacitadorInterno() || auth()->user()->hasAdminAccess())
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 space-y-6">
                        <div>
                            <h3 class="text-sm font-black text-Alumco-blue uppercase tracking-[0.2em]">Audiencia Objetivo</h3>
                            <p class="text-[10px] text-Alumco-gray/65 font-bold uppercase mt-1">Gestión de acceso por estamentos</p>
                        </div>
                        @livewire('capacitador.gestion-estamentos', ['curso' => $curso])
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modales --}}
    @include('capacitador.cursos.partials.modales-estructura')

@endsection

@push('scripts')
<script>
    // Drag & Drop Avanzado con Placeholders y Auto-creación de secciones
    (function () {
        const seccionesContainer = document.getElementById('secciones-container');
        let draggingModulo = null;
        let draggingSeccion = null;
        
        const modPlaceholder = document.createElement('div');
        modPlaceholder.className = 'modulo-placeholder';
        
        const secPlaceholder = document.createElement('div');
        secPlaceholder.className = 'seccion-placeholder';

        // --- DRAG DE MÓDULOS ---
        seccionesContainer.addEventListener('dragstart', e => {
            const modItem = e.target.closest('.modulo-item');
            if (modItem && !e.target.closest('.seccion-handle')) {
                draggingModulo = modItem;
                setTimeout(() => {
                    draggingModulo.classList.add('dragging');
                    seccionesContainer.classList.add('is-dragging-module');
                }, 0);
                e.dataTransfer.effectAllowed = 'move';
                return;
            }

            const secItem = e.target.closest('.seccion-item');
            if (secItem && e.target.closest('.seccion-handle')) {
                draggingSeccion = secItem;
                setTimeout(() => draggingSeccion.classList.add('dragging'), 0);
                e.dataTransfer.effectAllowed = 'move';
            }
        });

        seccionesContainer.addEventListener('dragend', () => {
            seccionesContainer.classList.remove('is-dragging-module');

            if (draggingModulo) {
                draggingModulo.classList.remove('dragging');
                if (modPlaceholder.parentNode) {
                    modPlaceholder.parentNode.replaceChild(draggingModulo, modPlaceholder);
                }
            }
            if (draggingSeccion) {
                draggingSeccion.classList.remove('dragging');
                if (secPlaceholder.parentNode) {
                    secPlaceholder.parentNode.replaceChild(draggingSeccion, secPlaceholder);
                }
            }
            
            document.querySelectorAll('.drag-over-section').forEach(el => el.classList.remove('drag-over-section'));
            document.querySelectorAll('.drag-over-list').forEach(el => el.classList.remove('drag-over-list'));
            
            draggingModulo = null;
            draggingSeccion = null;
            guardarEstructura();
        });

        seccionesContainer.addEventListener('dragover', e => {
            e.preventDefault();
            
            if (draggingModulo) {
                const targetList = e.target.closest('.modulos-list');
                const targetSec = e.target.closest('.seccion-item');
                const targetDZ = e.target.closest('.inter-section-dropzone');
                
                // Limpiar estados visuales
                document.querySelectorAll('.drag-over-section').forEach(el => el.classList.remove('drag-over-section'));
                document.querySelectorAll('.drag-over-list').forEach(el => el.classList.remove('drag-over-list'));
                document.querySelectorAll('.drag-over-dz').forEach(el => el.classList.remove('drag-over-dz'));
                
                if (targetDZ) {
                    targetDZ.classList.add('drag-over-dz');
                    targetDZ.appendChild(modPlaceholder);
                    return;
                }

                if (targetSec) targetSec.classList.add('drag-over-section');
                if (targetList) {
                    targetList.classList.add('drag-over-list');
                    const afterElement = getDragAfterElement(targetList, e.clientY, '.modulo-item:not(.dragging)');
                    if (afterElement == null) {
                        targetList.appendChild(modPlaceholder);
                    } else {
                        targetList.insertBefore(modPlaceholder, afterElement);
                    }
                }
            }

            if (draggingSeccion) {
                const targetSec = e.target.closest('.seccion-item');
                if (!targetSec || targetSec === draggingSeccion) return;
                
                const afterElement = getDragAfterElement(seccionesContainer, e.clientY, '.seccion-item:not(.dragging)');
                if (afterElement == null) {
                    seccionesContainer.appendChild(secPlaceholder);
                } else {
                    seccionesContainer.insertBefore(secPlaceholder, afterElement);
                }
            }
        });

        function getDragAfterElement(container, y, selector) {
            const draggableElements = [...container.querySelectorAll(selector)];
            return draggableElements.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset: offset, element: child };
                } else {
                    return closest;
                }
            }, { offset: Number.NEGATIVE_INFINITY }).element;
        }

        function guardarEstructura() {
            const data = {
                secciones: [],
                modulos_sueltos: []
            };

            // Recorrer todos los elementos del contenedor (incluyendo dropzones que ahora pueden tener módulos)
            const children = [...seccionesContainer.children];
            let needsReload = false;

            children.forEach(child => {
                // Caso 1: Una sección existente
                if (child.classList.contains('seccion-item')) {
                    const secId = child.dataset.seccionId;
                    const modulos = [...child.querySelectorAll('.modulo-item')].map(m => parseInt(m.dataset.id));
                    
                    if (secId === 'null') {
                        data.modulos_sueltos = modulos;
                    } else {
                        data.secciones.push({ id: parseInt(secId), modulos: modulos });
                    }
                } 
                // Caso 2: Una dropzone que recibió módulos (Auto-creación)
                else if (child.classList.contains('inter-section-dropzone')) {
                    const modulos = [...child.querySelectorAll('.modulo-item')].map(m => parseInt(m.dataset.id));
                    if (modulos.length > 0) {
                        data.secciones.push({ id: child.dataset.seccionId, modulos: modulos });
                        needsReload = true; // Forzar reload para obtener IDs reales
                    }
                }
            });

            fetch('{{ route('capacitador.cursos.secciones.reordenar', $curso) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            }).then(response => response.json())
              .then(res => {
                  if (res.status === 'success' && needsReload) {
                      window.location.reload();
                  }
              });
        }
    })();

    // Gestión de Modales
    function openSeccionModal(id = null, titulo = '') {
        const modal = document.getElementById('seccion-modal');
        const form = document.getElementById('seccion-form');
        const input = document.getElementById('seccion-titulo');
        const method = document.getElementById('seccion-method');
        
        if (id) {
            form.action = `{{ route('capacitador.cursos.secciones.store', $curso) }}/${id}`;
            method.value = 'PUT';
            document.getElementById('seccion-modal-title').innerText = 'Editar Sección';
        } else {
            form.action = `{{ route('capacitador.cursos.secciones.store', $curso) }}`;
            method.value = 'POST';
            document.getElementById('seccion-modal-title').innerText = 'Nueva Sección';
        }
        
        input.value = titulo;
        modal.classList.remove('hidden');
        setTimeout(() => input.focus(), 100);
    }

    function closeSeccionModal() {
        document.getElementById('seccion-modal').classList.add('hidden');
    }
</script>
@endpush
