<div class="space-y-4">
    <div>
        <h2 class="font-display text-xl font-black text-Alumco-gray">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm font-semibold leading-relaxed text-Alumco-gray/65">{{ $description }}</p>
        @endif
    </div>

    <div class="accessibility-toggle">
        <div>
            <p class="accessibility-toggle-title">Tamaño de texto</p>
            <p class="accessibility-toggle-help">Actual: {{ $this->fontLabel() }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button"
                    wire:click="decreaseFont"
                    @disabled($fontLevel === 0)
                    class="worker-focus flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-sm font-black text-Alumco-gray shadow-sm hover:border-Alumco-blue/30 hover:text-Alumco-blue disabled:cursor-not-allowed disabled:opacity-30"
                    aria-label="Reducir tamaño de texto">
                A-
            </button>
            <button type="button"
                    wire:click="increaseFont"
                    @disabled($fontLevel === 3)
                    class="worker-focus flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-base font-black text-Alumco-gray shadow-sm hover:border-Alumco-blue/30 hover:text-Alumco-blue disabled:cursor-not-allowed disabled:opacity-30"
                    aria-label="Aumentar tamaño de texto">
                A+
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
