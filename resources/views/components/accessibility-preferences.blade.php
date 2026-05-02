@props([
    'title' => 'Preferencias de accesibilidad',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    <div>
        <h2 class="font-display text-xl font-black text-Alumco-gray">{{ $title }}</h2>
        @if ($description)
            <p class="mt-1 text-sm font-semibold leading-relaxed text-Alumco-gray/65">{{ $description }}</p>
        @endif
    </div>

    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-black text-Alumco-gray">Tamaño de texto</p>
                <p class="mt-0.5 text-sm font-semibold text-Alumco-gray/60">
                    Actual: <span x-text="$store.fontScale.currentLabel()"></span>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        x-on:click="$store.fontScale.decrease()"
                        :disabled="$store.fontScale.level === 0"
                        class="worker-focus flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-sm font-black text-Alumco-gray shadow-sm hover:border-Alumco-blue/30 hover:text-Alumco-blue disabled:cursor-not-allowed disabled:opacity-30"
                        aria-label="Reducir tamaño de texto">
                    A−
                </button>
                <button type="button"
                        x-on:click="$store.fontScale.increase()"
                        :disabled="$store.fontScale.level === 3"
                        class="worker-focus flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-base font-black text-Alumco-gray shadow-sm hover:border-Alumco-blue/30 hover:text-Alumco-blue disabled:cursor-not-allowed disabled:opacity-30"
                        aria-label="Aumentar tamaño de texto">
                    A+
                </button>
            </div>
        </div>
    </div>

    <div class="space-y-2">
        <label class="accessibility-toggle">
            <span>
                <span class="accessibility-toggle-title">Alto contraste</span>
                <span class="accessibility-toggle-help">Refuerza bordes, texto y fondos.</span>
            </span>
            <input type="checkbox"
                   x-model="$store.accessibility.highContrast"
                   x-on:change="$store.accessibility.persist()"
                   class="h-5 w-5 rounded border-gray-300 text-Alumco-blue focus:ring-Alumco-blue">
        </label>

        <label class="accessibility-toggle">
            <span>
                <span class="accessibility-toggle-title">Reducir movimiento</span>
                <span class="accessibility-toggle-help">Desactiva transiciones y animaciones.</span>
            </span>
            <input type="checkbox"
                   x-model="$store.accessibility.reducedMotion"
                   x-on:change="$store.accessibility.persist()"
                   class="h-5 w-5 rounded border-gray-300 text-Alumco-blue focus:ring-Alumco-blue">
        </label>

        <label class="accessibility-toggle">
            <span>
                <span class="accessibility-toggle-title">Fondo simple</span>
                <span class="accessibility-toggle-help">Quita la textura ligera del fondo.</span>
            </span>
            <input type="checkbox"
                   x-model="$store.accessibility.simpleBackground"
                   x-on:change="$store.accessibility.persist()"
                   class="h-5 w-5 rounded border-gray-300 text-Alumco-blue focus:ring-Alumco-blue">
        </label>

        <label class="accessibility-toggle">
            <span>
                <span class="accessibility-toggle-title">Tarjetas compactas</span>
                <span class="accessibility-toggle-help">Reduce portadas y espaciado en listados.</span>
            </span>
            <input type="checkbox"
                   x-model="$store.accessibility.compactCards"
                   x-on:change="$store.accessibility.persist()"
                   class="h-5 w-5 rounded border-gray-300 text-Alumco-blue focus:ring-Alumco-blue">
        </label>
    </div>
</div>
