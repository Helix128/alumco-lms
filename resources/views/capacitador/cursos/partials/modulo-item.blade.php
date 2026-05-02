<div class="modulo-item flex items-center gap-4 bg-white border border-gray-100 rounded-2xl p-4 hover:border-Alumco-blue/30 hover:shadow-md transition-all group"
     data-id="{{ $modulo->id }}" draggable="true">
    
    {{-- Drag handle --}}
    <div class="cursor-grab active:cursor-grabbing p-2 text-gray-200 group-hover:text-Alumco-blue/20 transition-colors">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 6h2v2H8V6zm0 4h2v2H8v-2zm0 4h2v2H8v-2zm6-8h2v2h-2V6zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z"/>
        </svg>
    </div>

    {{-- Icono según tipo --}}
    <div class="w-10 h-10 rounded-xl bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center font-display font-black text-xs shrink-0 border border-Alumco-blue/10">
        @switch($modulo->tipo_contenido)
            @case('video')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                @break
            @case('evaluacion')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @break
            @default
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        @endswitch
    </div>

    {{-- Info --}}
    <div class="flex-1 min-w-0">
        <h4 class="font-bold text-Alumco-gray text-sm truncate group-hover:text-Alumco-blue transition-colors">
            {{ $modulo->titulo }}
        </h4>
        <div class="flex items-center gap-3 mt-1">
            <span class="text-[10px] font-black uppercase tracking-widest text-Alumco-gray/65 bg-gray-50 px-2 py-0.5 rounded-md">
                {{ \App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido }}
            </span>
            @if ($modulo->duracion_minutos)
                <span class="text-[10px] font-bold text-Alumco-gray/65 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-12 0 9 9 0 0112 0z"></path></svg>
                    {{ $modulo->duracion_minutos }} min
                </span>
            @endif
        </div>
    </div>

    {{-- Acciones --}}
    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <a href="{{ route('capacitador.cursos.modulos.editar', [$curso, $modulo]) }}"
           class="p-2 text-Alumco-gray/65 hover:text-Alumco-blue hover:bg-Alumco-blue/5 rounded-lg transition-all" title="Propiedades">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        </a>
        @if ($modulo->tipo_contenido === 'evaluacion' && $modulo->evaluacion)
            <a href="{{ route('capacitador.cursos.modulos.evaluacion', [$curso, $modulo]) }}"
               class="p-2 text-Alumco-gray/65 hover:text-Alumco-green-vivid hover:bg-Alumco-green-vivid/5 rounded-lg transition-all" title="Gestionar Evaluación">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </a>
        @endif
        <form action="{{ route('capacitador.cursos.modulos.destroy', [$curso, $modulo]) }}" method="POST"
              onsubmit="return confirm('¿Eliminar este módulo?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="p-2 text-Alumco-gray/65 hover:text-Alumco-coral hover:bg-Alumco-coral/5 rounded-lg transition-all" title="Eliminar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            </button>
        </form>
    </div>
</div>
