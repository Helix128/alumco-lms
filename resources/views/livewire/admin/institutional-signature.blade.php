<div class="admin-surface p-8">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h3 class="text-xl font-display font-black text-Alumco-blue">Firma institucional</h3>
            <p class="text-sm text-Alumco-gray/60 mt-2">Firma del representante legal para certificados emitidos por Alumco.</p>
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
        <div class="space-y-4">
            <label class="block text-sm font-bold text-Alumco-gray" for="firma-representante-legal">Firma del Representante Legal</label>

            <div class="relative group">
                <input type="file"
                       wire:model="firma_representante_legal"
                       id="firma-representante-legal"
                       accept="image/*"
                       class="w-full bg-Alumco-cream/30 border border-dashed border-gray-200 rounded-xl px-4 py-8 text-sm file:hidden cursor-pointer hover:bg-Alumco-blue/5 transition-all text-center font-bold text-Alumco-gray/40">
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none gap-2">
                    <svg class="w-6 h-6 text-Alumco-blue/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span class="text-[10px] uppercase tracking-widest font-display">Subir firma escaneada (PNG transparente recomendado)</span>
                </div>
            </div>
            @error('firma_representante_legal') <span class="text-Alumco-coral text-xs font-bold">{{ $message }}</span> @enderror
        </div>

        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 flex flex-col items-center justify-center">
            <span class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-4">Vista Previa Firma Actual</span>
            @if ($firma_representante_legal)
                <img src="{{ $firma_representante_legal->temporaryUrl() }}" class="h-24 object-contain mix-blend-multiply" alt="Vista previa de la firma institucional">
            @elseif ($firma_actual)
                <img src="{{ asset('storage/'.$firma_actual) }}" class="h-24 object-contain mix-blend-multiply" alt="Firma institucional actual">
            @else
                <div class="h-24 flex items-center justify-center border-2 border-dashed border-gray-200 rounded-xl w-full">
                    <span class="text-[10px] text-gray-300 font-bold uppercase italic">Sin firma cargada</span>
                </div>
            @endif
        </div>
    </div>

    <div class="pt-6 mt-6 border-t border-gray-50 flex justify-end">
        <button wire:click="guardar"
                wire:loading.attr="disabled"
                class="bg-Alumco-blue hover:bg-Alumco-blue/90 disabled:opacity-60 text-white font-display font-bold py-3 px-8 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95 flex items-center gap-2">
            <svg wire:loading.remove wire:target="guardar, firma_representante_legal" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            <svg wire:loading wire:target="guardar, firma_representante_legal" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span wire:loading.remove wire:target="guardar, firma_representante_legal">Guardar firma institucional</span>
            <span wire:loading wire:target="guardar, firma_representante_legal">Procesando...</span>
        </button>
    </div>
</div>
