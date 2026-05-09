<div class="admin-surface p-6">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h4 class="admin-page-eyebrow">Firma personal</h4>
            <p class="text-sm text-Alumco-gray/60 mt-2">Se usará en los certificados de los cursos que impartas.</p>
        </div>
        <x-saving-indicator />
    </div>

    @if ($mensaje)
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ $mensaje }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
        <div class="space-y-3">
            <label for="firma-digital" class="block text-sm font-bold text-Alumco-gray">Firma digital</label>
            <input type="file"
                   wire:model="firma_digital"
                   id="firma-digital"
                   accept="image/*"
                   class="w-full bg-Alumco-cream/30 border border-dashed border-gray-200 rounded-xl px-4 py-6 text-sm file:hidden cursor-pointer hover:bg-Alumco-blue/5 transition-all text-center font-bold text-Alumco-gray/40">
            @error('firma_digital') <span class="text-Alumco-coral text-xs font-bold">{{ $message }}</span> @enderror
        </div>

        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex flex-col items-center justify-center">
            <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-4">Vista Previa Firma Actual</span>
            @if ($firma_digital)
                <img src="{{ $firma_digital->temporaryUrl() }}" class="h-20 object-contain mix-blend-multiply" alt="Vista previa de tu firma">
            @elseif ($firma_actual)
                <img src="{{ asset('storage/'.$firma_actual) }}" class="h-20 object-contain mix-blend-multiply" alt="Tu firma actual">
            @else
                <div class="h-20 flex items-center justify-center border-2 border-dashed border-gray-200 rounded-xl w-full">
                    <span class="text-[10px] text-gray-300 font-bold uppercase italic">Sin firma cargada</span>
                </div>
            @endif
        </div>
    </div>

    <div class="pt-6 mt-6 border-t border-gray-50 flex justify-end">
        <button wire:click="guardar"
                wire:loading.attr="disabled"
                class="bg-Alumco-blue hover:bg-Alumco-blue/90 disabled:opacity-60 text-white font-display font-bold py-3 px-6 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95 flex items-center gap-2">
            <span wire:loading.remove wire:target="guardar, firma_digital">Guardar firma</span>
            <span wire:loading wire:target="guardar, firma_digital">Procesando...</span>
        </button>
    </div>
</div>
