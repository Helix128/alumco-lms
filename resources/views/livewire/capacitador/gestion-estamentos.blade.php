<div>
    @if (session()->has('success'))
        <x-alert type="success" :message="session('success')" class="mb-6" />
    @endif

    <div class="space-y-8">
        {{-- Estamentos asignados --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <h4 class="text-[11px] font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Estamentos con acceso a la capacitación</h4>
                <div class="h-px bg-gray-100 flex-1"></div>
            </div>
            
            <div class="flex flex-wrap gap-2">
                @php $asignados = $todos->whereIn('id', $seleccionados); @endphp
                @forelse ($asignados as $estamento)
                    <div wire:key="asignado-{{ $estamento->id }}" class="inline-flex items-center gap-2 bg-Alumco-blue text-white px-4 py-1.5 rounded-full shadow-sm hover:shadow-md transition-all group">
                        <span class="text-xs font-bold">{{ $estamento->nombre }}</span>
                        <button wire:click="toggleEstamento({{ $estamento->id }})" 
                                wire:loading.attr="disabled"
                                class="w-4 h-4 rounded-full bg-white/20 flex items-center justify-center hover:bg-Alumco-coral transition-colors disabled:opacity-50"
                                title="Quitar acceso">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @empty
                    <div class="w-full py-8 border-2 border-dashed border-gray-100 rounded-2xl flex flex-col items-center justify-center gap-2">
                        <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <p class="text-sm text-Alumco-gray/30 font-bold uppercase tracking-widest">Sin estamentos asignados</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Estamentos disponibles --}}
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <h4 class="text-[11px] font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Disponibles para agregar</h4>
                <div class="h-px bg-gray-100 flex-1"></div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @php $disponibles = $todos->whereNotIn('id', $seleccionados); @endphp
                @forelse ($disponibles as $estamento)
                    <button wire:key="disponible-{{ $estamento->id }}" 
                            wire:click="toggleEstamento({{ $estamento->id }})" 
                            wire:loading.attr="disabled"
                            class="flex items-center justify-center px-4 py-2.5 bg-white border border-gray-100 rounded-xl text-xs font-bold text-Alumco-gray/60 hover:border-Alumco-blue hover:text-Alumco-blue hover:shadow-sm transition-all text-center disabled:opacity-50">
                        {{ $estamento->nombre }}
                    </button>
                @empty
                    <p class="col-span-full text-center text-xs text-Alumco-gray/30 font-medium">Todos los estamentos han sido asignados.</p>
                @endforelse
            </div>
        </div>

        <div class="pt-6 border-t border-gray-50 flex justify-end">
            <button wire:click="guardar" 
                    wire:loading.attr="disabled"
                    class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-bold py-3 px-8 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95 disabled:opacity-50">
                <span wire:loading.remove wire:target="guardar">Guardar cambios en asignación</span>
                <span wire:loading wire:target="guardar">Guardando...</span>
            </button>
        </div>
    </div>
</div>
