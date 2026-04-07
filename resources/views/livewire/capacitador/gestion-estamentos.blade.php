<div>
    @if ($flashMensaje)
        <div class="mb-4 px-4 py-2 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ $flashMensaje }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Estamentos asignados --}}
        <div>
            <h4 class="font-bold text-Alumco-gray mb-3 text-sm">Estamentos asignados</h4>
            @php $asignados = $todos->whereIn('id', $seleccionados); @endphp
            @forelse ($asignados as $estamento)
                <div class="flex items-center justify-between bg-Alumco-blue/10 border border-Alumco-blue/20
                            rounded-lg px-3 py-2 mb-2">
                    <span class="text-sm font-semibold text-Alumco-blue">{{ $estamento->nombre }}</span>
                    <button wire:click="toggleEstamento({{ $estamento->id }})"
                            class="text-red-400 hover:text-red-600 transition-colors text-xs font-semibold">
                        Remover
                    </button>
                </div>
            @empty
                <p class="text-Alumco-gray/50 text-sm">Sin estamentos asignados.</p>
            @endforelse
        </div>

        {{-- Estamentos disponibles --}}
        <div>
            <h4 class="font-bold text-Alumco-gray mb-3 text-sm">Disponibles</h4>
            @php $disponibles = $todos->whereNotIn('id', $seleccionados); @endphp
            @forelse ($disponibles as $estamento)
                <div class="flex items-center justify-between bg-white border border-gray-200
                            rounded-lg px-3 py-2 mb-2">
                    <span class="text-sm text-Alumco-gray">{{ $estamento->nombre }}</span>
                    <button wire:click="toggleEstamento({{ $estamento->id }})"
                            class="text-Alumco-blue hover:underline text-xs font-semibold transition-colors">
                        Agregar
                    </button>
                </div>
            @empty
                <p class="text-Alumco-gray/50 text-sm">Todos asignados.</p>
            @endforelse
        </div>
    </div>

    <div class="flex justify-end mt-4">
        <button wire:click="guardar"
                class="bg-Alumco-blue text-white px-5 py-2 rounded-lg font-semibold
                       hover:brightness-110 transition text-sm">
            Guardar asignaciones
        </button>
    </div>
</div>
