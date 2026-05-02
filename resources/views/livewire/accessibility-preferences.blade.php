<div class="space-y-4">
    <div>
        <h2 class="font-display text-xl font-black text-Alumco-gray">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm font-semibold leading-relaxed text-Alumco-gray/65">{{ $description }}</p>
        @endif
    </div>

    <div class="accessibility-toggle flex-col !items-stretch gap-3">
        <div>
            <p class="accessibility-toggle-title">Tamaño de texto</p>
            <p class="accessibility-toggle-help">Actual: {{ $this->fontLabel() }}</p>
        </div>
        <div class="grid grid-cols-3 gap-1 bg-gray-100 p-1 rounded-2xl">
            <button type="button"
                    wire:click="setFontLevel(0)"
                    @class([
                        'flex items-center justify-center py-2.5 rounded-xl text-sm font-black transition-all',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 0,
                        'text-gray-500 hover:text-Alumco-gray' => $fontLevel !== 0,
                    ])>
                Normal
            </button>
            <button type="button"
                    wire:click="setFontLevel(1)"
                    @class([
                        'flex items-center justify-center py-2.5 rounded-xl text-sm font-black transition-all',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 1,
                        'text-gray-500 hover:text-Alumco-gray' => $fontLevel !== 1,
                    ])>
                Grande
            </button>
            <button type="button"
                    wire:click="setFontLevel(2)"
                    @class([
                        'flex items-center justify-center py-2.5 rounded-xl text-sm font-black transition-all',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 2,
                        'text-gray-500 hover:text-Alumco-gray' => $fontLevel !== 2,
                    ])>
                Extra
            </button>
        </div>
    </div>

    <div class="space-y-2">
        <label class="accessibility-toggle">
            <span>
                <span class="accessibility-toggle-title">Alto contraste</span>
                <span class="accessibility-toggle-help">Refuerza bordes, texto y fondos.</span>
            </span>
            <input type="checkbox"
                   wire:model.boolean="highContrast"
                   wire:change="save"
                   class="h-5 w-5 rounded border-gray-300 text-Alumco-blue focus:ring-Alumco-blue">
        </label>

        <label class="accessibility-toggle">
            <span>
                <span class="accessibility-toggle-title">Reducir movimiento</span>
                <span class="accessibility-toggle-help">Desactiva transiciones y animaciones.</span>
            </span>
            <input type="checkbox"
                   wire:model.boolean="reducedMotion"
                   wire:change="save"
                   class="h-5 w-5 rounded border-gray-300 text-Alumco-blue focus:ring-Alumco-blue">
        </label>
    </div>
</div>
