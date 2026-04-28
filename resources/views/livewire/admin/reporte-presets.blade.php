<div class="flex items-center gap-3" 
     x-data="{ 
        open: false, 
        creating: false,
        deletingId: null,
        init() {
            this.$watch('creating', value => { if(value) this.$nextTick(() => $refs.nuevoNombreInput.focus()) });
        }
     }"
     @preset-guardado.window="creating = false"
>
    {{-- Contenedor Dropdown --}}
    <div class="relative" @click.away="open = false; deletingId = null">
        <button 
            type="button"
            @click="open = !open"
            class="flex items-center gap-3 pl-4 pr-3 py-3 bg-white border border-gray-200 rounded-2xl text-sm font-bold text-Alumco-blue shadow-lg shadow-gray-100/50 hover:border-Alumco-blue/30 transition-all active:scale-95"
        >
            <svg class="w-5 h-5 text-Alumco-blue/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span>{{ $presets->count() > 0 ? 'Plantillas' : 'Sin plantillas' }}</span>
            <svg class="w-4 h-4 text-Alumco-blue/20 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        {{-- Menú del Dropdown (Hacia arriba para no salirse del modal) --}}
        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="absolute bottom-full left-0 mb-2 w-72 bg-white border border-gray-100 rounded-2xl shadow-[0_-10px_40px_rgba(0,0,0,0.1)] z-[60] py-2 overflow-hidden"
            style="display: none;"
        >
            {{-- Botón Crear Nuevo --}}
            <button 
                type="button"
                @click="creating = true; open = false"
                class="w-full flex items-center gap-3 px-4 py-3 text-xs font-black text-Alumco-blue uppercase tracking-widest hover:bg-Alumco-blue/5 transition border-b border-gray-50"
            >
                <div class="w-6 h-6 rounded-lg bg-Alumco-blue/10 flex items-center justify-center text-Alumco-blue">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
                Nueva Plantilla
            </button>

            {{-- Lista de Presets --}}
            <div class="max-h-64 overflow-y-auto custom-scrollbar">
                @forelse($presets as $p)
                    <div class="group flex items-center justify-between px-2 py-1">
                        <button 
                            type="button"
                            @click="selectedKeys = [...@js($p->columnas)]; open = false; $wire.resetError('nuevoNombre'); $wire.nuevoNombre = ''"
                            class="flex-1 text-left px-3 py-2 text-xs text-Alumco-gray font-bold rounded-xl group-hover:bg-Alumco-blue/5 transition"
                        >
                            {{ $p->nombre }}
                        </button>
                        <button 
                            type="button"
                            @click.stop="deletingId = {{ $p->id }}"
                            class="p-2 text-gray-400 hover:text-Alumco-coral hover:bg-Alumco-coral/5 rounded-xl transition-all"
                            title="Eliminar"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center">
                        <p class="text-[10px] font-bold text-gray-300 uppercase tracking-widest">No hay plantillas</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Modal de Confirmación UI --}}
        <div x-show="deletingId !== null" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="absolute bottom-full left-0 mb-2 w-72 h-48 z-[70] bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center p-4 text-center rounded-2xl border border-gray-100 shadow-xl"
             style="display: none;"
             @click.stop
        >
            <p class="text-[10px] font-black text-Alumco-blue uppercase tracking-widest mb-3">¿Eliminar formato?</p>
            <div class="flex gap-2">
                <button type="button" 
                        @click="deletingId = null" 
                        class="px-3 py-1.5 text-[9px] font-black text-gray-400 hover:text-Alumco-blue uppercase tracking-tighter transition-colors">
                    Cancelar
                </button>
                <button type="button" 
                        @click="$wire.eliminarPreset(deletingId); deletingId = null" 
                        class="px-4 py-1.5 bg-Alumco-coral text-white text-[9px] font-black rounded-lg shadow-lg shadow-Alumco-coral/20 hover:bg-Alumco-coral/90 uppercase tracking-widest transition-all">
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    {{-- Input de Creación --}}
    <div 
        x-show="creating" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-x-4"
        x-transition:enter-end="opacity-100 translate-x-0"
        class="flex items-center gap-2"
        style="display: none;"
    >
        <input 
            type="text" 
            x-ref="nuevoNombreInput"
            wire:model="nuevoNombre"
            placeholder="Nombre..."
            class="w-56 px-4 py-3 bg-white border border-gray-200 rounded-2xl text-xs font-bold focus:ring-2 focus:ring-Alumco-blue/20 focus:border-Alumco-blue/30 outline-none @error('nuevoNombre') border-Alumco-coral @enderror"
            @keydown.enter.prevent="$wire.guardarPreset(selectedKeys)"
            @keydown.escape="creating = false"
        >
        <button 
            type="button"
            @click="$wire.guardarPreset(selectedKeys)"
            class="bg-Alumco-blue text-white px-4 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-Alumco-blue/90 transition-all shadow-lg shadow-Alumco-blue/10 active:scale-95"
        >
            OK
        </button>
        <button 
            type="button"
            @click="creating = false; $wire.resetError('nuevoNombre'); $wire.nuevoNombre = ''"
            class="p-2 text-gray-300 hover:text-Alumco-coral transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    @error('nuevoNombre') 
        <div class="text-[9px] text-Alumco-coral font-black uppercase tracking-tighter bg-Alumco-coral/5 px-3 py-2 rounded-xl border border-Alumco-coral/10">
            {{ $message }}
        </div>
    @enderror
</div>
