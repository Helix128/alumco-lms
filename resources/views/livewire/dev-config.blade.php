<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <div class="mb-8">
        <h3 class="text-xl font-display font-black text-Alumco-blue">Variables Globales del Sistema</h3>
        <p class="text-sm text-Alumco-gray/60 mt-2">Parámetros de la lógica de negocio</p>
    </div>

    @if ($mensaje)
        <div class="mb-6 px-4 py-3 bg-green-50 border border-green-100 text-green-700 rounded-xl text-sm font-medium flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            {{ $mensaje }}
        </div>
    @endif

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-2">
                <label class="block text-sm font-bold text-Alumco-gray">Porcentaje de Aprobación (%)</label>
                <div class="flex items-center gap-4">
                    <input type="number" wire:model="puntos_aprobacion" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-Alumco-blue/10 outline-none transition-all"
                           min="0" max="100">
                    <span class="text-xs text-Alumco-gray/40 font-bold whitespace-nowrap">Valor actual: {{ $puntos_aprobacion }}%</span>
                </div>
                <p class="text-[11px] text-Alumco-gray/40 italic mt-1">Define el puntaje mínimo para que una evaluación se considere aprobada.</p>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-bold text-Alumco-gray">Intentos Máximos Semanales</label>
                <div class="flex items-center gap-4">
                    <input type="number" wire:model="max_intentos_semanales" 
                           class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-4 focus:ring-Alumco-blue/10 outline-none transition-all"
                           min="1" max="50">
                    <span class="text-xs text-Alumco-gray/40 font-bold whitespace-nowrap">Valor actual: {{ $max_intentos_semanales }}</span>
                </div>
                <p class="text-[11px] text-Alumco-gray/40 italic mt-1">Límite de veces que un usuario puede rendir la misma evaluación en una semana.</p>
            </div>
        </div>

        <div class="pt-6 border-t border-gray-50 flex justify-end">
            <button wire:click="guardar" 
                    class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-bold py-3 px-8 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95 flex items-center gap-2">
                <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                </svg>
                <svg wire:loading class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Guardar Configuración Global</span>
            </button>
        </div>
    </div>
</div>
