<div class="space-y-6">
    <div>
        <h2 class="font-display text-xl font-black text-Alumco-gray">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm font-semibold leading-relaxed text-Alumco-gray/65">{{ $description }}</p>
        @endif
    </div>

    <div class="space-y-3">
        <div>
            <p class="text-sm font-black uppercase tracking-widest text-Alumco-gray/40">Tamaño de texto</p>
        </div>
        <div class="flex flex-col gap-1.5 sm:grid sm:grid-cols-3 bg-gray-100/80 p-1.5 rounded-2xl">
            <button type="button"
                    wire:click="setFontLevel(0)"
                    @class([
                        'flex items-center justify-center py-3 sm:py-2.5 rounded-xl text-sm font-black transition-all cursor-pointer',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 0,
                        'text-gray-500 hover:text-Alumco-gray hover:bg-white/50' => $fontLevel !== 0,
                    ])>
                Normal
            </button>
            <button type="button"
                    wire:click="setFontLevel(1)"
                    @class([
                        'flex items-center justify-center py-3 sm:py-2.5 rounded-xl text-sm font-black transition-all cursor-pointer',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 1,
                        'text-gray-500 hover:text-Alumco-gray hover:bg-white/50' => $fontLevel !== 1,
                    ])>
                Grande
            </button>
            <button type="button"
                    wire:click="setFontLevel(2)"
                    @class([
                        'flex items-center justify-center py-3 sm:py-2.5 rounded-xl text-sm font-black transition-all cursor-pointer',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 2,
                        'text-gray-500 hover:text-Alumco-gray hover:bg-white/50' => $fontLevel !== 2,
                    ])>
                Extragrande
            </button>
        </div>
    </div>

    <div class="space-y-4">
        {{-- Toggle: Alto Contraste --}}
        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50/50 ring-1 ring-gray-100">
            <div class="flex flex-col">
                <span class="text-base font-black text-Alumco-gray">Alto contraste</span>
                <span class="text-xs font-semibold text-Alumco-gray/50">Refuerza bordes, texto y fondos.</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox"
                       wire:model.boolean="highContrast"
                       wire:change="save"
                       class="sr-only peer">
                <div class="w-12 h-6.5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:start-[3px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-Alumco-blue"></div>
            </label>
        </div>

        {{-- Toggle: Reducir Movimiento --}}
        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50/50 ring-1 ring-gray-100">
            <div class="flex flex-col">
                <span class="text-base font-black text-Alumco-gray">Reducir movimiento</span>
                <span class="text-xs font-semibold text-Alumco-gray/50">Desactiva transiciones y animaciones.</span>
            </div>
            <label class="relative inline-flex items-center cursor-pointer select-none">
                <input type="checkbox"
                       wire:model.boolean="reducedMotion"
                       wire:change="save"
                       class="sr-only peer">
                <div class="w-12 h-6.5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:start-[3px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-Alumco-blue"></div>
            </label>
        </div>
    </div>
</div>
