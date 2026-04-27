@extends('layouts.panel')

@section('title', 'Cursos')
@section('header_title', 'Cursos y Material')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h2 class="text-xl font-display font-bold text-Alumco-blue/70">Capacitaciones</h2>
    </div>

    <a href="{{ route('capacitador.cursos.crear') }}"
       class="w-full sm:w-auto bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-bold py-3 px-8 rounded-2xl shadow-lg shadow-Alumco-blue/20 flex items-center justify-center transition-all active:scale-95">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Nuevo curso
    </a>
</div>

<!-- Tabla Card-Style -->
<div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 hidden md:table-cell">Curso</th>
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Capacitador</th>
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-center">Estructura</th>
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-center">Estado</th>
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($cursos as $curso)
                    @php
                        $tienePlanActiva = $curso->planificaciones_count > 0;
                        $estado = $tienePlanActiva ? 'Programado' : 'Sin Programar';
                        $badgeColor = $tienePlanActiva ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400';
                    @endphp
                    <tr class="hover:bg-Alumco-cream/30 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                @if ($curso->imagen_portada)
                                    <img src="{{ asset('storage/' . $curso->imagen_portada) }}"
                                         class="w-14 h-10 object-cover rounded-xl shadow-sm border border-gray-100">
                                @else
                                    <div class="w-14 h-10 bg-Alumco-blue/5 rounded-xl border border-Alumco-blue/10 flex items-center justify-center text-Alumco-blue/30">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <a href="{{ route('capacitador.cursos.show', $curso) }}" class="font-display font-bold text-Alumco-gray group-hover:text-Alumco-blue transition-colors leading-tight block">
                                        {{ $curso->titulo }}
                                    </a>
                                    <span class="text-[10px] text-Alumco-gray/40 font-bold uppercase tracking-wider">ID: #{{ str_pad($curso->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-8 py-5">
                            @if($curso->capacitador_id === auth()->id())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-Alumco-blue text-white shadow-sm">
                                    Tú (Autor)
                                </span>
                            @else
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-Alumco-gray leading-tight">{{ $curso->capacitador->name }}</span>
                                    <span class="text-[10px] text-Alumco-gray/40 font-bold uppercase tracking-widest">Colaborador</span>
                                </div>
                            @endif
                        </td>

                        <td class="px-8 py-5 text-center">
                            <div class="flex items-center justify-center gap-4">
                                <div class="text-center">
                                    <span class="block text-sm font-black text-Alumco-blue">{{ $curso->modulos_count }}</span>
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-tighter">Módulos</span>
                                </div>
                                <div class="w-px h-6 bg-gray-100"></div>
                                <div class="text-center">
                                    <span class="block text-sm font-black text-Alumco-blue">{{ $curso->estamentos_count }}</span>
                                    <span class="block text-[9px] font-black text-gray-400 uppercase tracking-tighter">Roles</span>
                                </div>
                            </div>
                        </td>

                        <td class="px-8 py-5 text-center">
                            <span class="inline-block px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $badgeColor }}">
                                {{ $estado }}
                            </span>
                        </td>

                        <td class="px-8 py-5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('capacitador.cursos.show', $curso) }}" 
                                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-Alumco-gray hover:bg-Alumco-blue hover:text-white transition-all shadow-sm" title="Gestionar Contenido">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>
                                <a href="{{ route('capacitador.cursos.editar', $curso) }}" 
                                   class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-Alumco-gray hover:bg-amber-500 hover:text-white transition-all shadow-sm" title="Editar Detalles">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>
                                
                                {{-- Botón Duplicar --}}
                                <button type="button" 
                                        onclick="openDuplicateModal('{{ route('capacitador.cursos.duplicar', $curso) }}', '{{ addslashes($curso->titulo) }}')" 
                                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-Alumco-gray hover:bg-Alumco-blue hover:text-white transition-all shadow-sm" 
                                        title="Duplicar / Nueva Versión">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                    </svg>
                                </button>

                                <form action="{{ route('capacitador.cursos.destroy', $curso) }}" method="POST"
                                      onsubmit="return confirm('¿Eliminar este curso? Esta acción no se puede deshacer.')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl bg-gray-50 text-Alumco-gray hover:bg-Alumco-coral hover:text-white transition-all shadow-sm" title="Eliminar Curso">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center opacity-40">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <p class="font-display font-bold">No has creado cursos aún</p>
                                <a href="{{ route('capacitador.cursos.crear') }}" class="text-sm font-bold text-Alumco-blue hover:underline mt-2">Crear el primero</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($cursos->hasPages())
    <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
        {{ $cursos->links() }}
    </div>
    @endif
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
</script>
@endsection
