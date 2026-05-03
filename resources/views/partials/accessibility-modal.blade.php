@props([
    'buttonClass' => 'worker-focus inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-black text-Alumco-blue shadow-sm ring-1 ring-Alumco-blue/10',
    'showLabel' => true,
])

<div x-data="{ accessibilityOpen: false }"
     x-on:keydown.escape.window="accessibilityOpen = false">
    <button type="button"
            x-on:click="accessibilityOpen = true"
            class="{{ $buttonClass }}"
            aria-haspopup="dialog"
            :aria-expanded="accessibilityOpen.toString()">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9M10.5 6a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0ZM4.5 12h9m0 0a2.25 2.25 0 1 0 4.5 0 2.25 2.25 0 0 0-4.5 0Zm-9 6h9m0 0a2.25 2.25 0 1 0 4.5 0 2.25 2.25 0 0 0-4.5 0Z" />
        </svg>
        @if ($showLabel)
            <span>Opciones</span>
        @endif
    </button>

    <template x-teleport="body">
        <div x-cloak
             x-show="accessibilityOpen"
             class="fixed inset-0 z-[110] flex items-end justify-center p-4 sm:items-center"
             role="dialog"
             aria-modal="true"
             aria-label="Opciones de accesibilidad">
            <div x-show="accessibilityOpen"
                 x-transition.opacity
                 class="absolute inset-0 bg-Alumco-gray/60 backdrop-blur-sm"
                 x-on:click="accessibilityOpen = false"></div>

            <div x-show="accessibilityOpen"
                 x-transition
                 class="relative max-h-[88vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-5 shadow-2xl ring-1 ring-Alumco-blue/10 sm:p-6">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <p class="font-display text-2xl font-black text-Alumco-gray">Opciones</p>
                        <p class="mt-1 text-sm font-semibold text-Alumco-gray/65">Tus preferencias se guardan en tu cuenta.</p>
                    </div>
                    <button type="button"
                            x-on:click="accessibilityOpen = false"
                            class="worker-focus flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-50 text-Alumco-gray hover:bg-gray-100"
                            aria-label="Cerrar opciones">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <livewire:accessibility-preferences title="Preferencias de accesibilidad" description="Ajusta la interfaz para leer y navegar con mayor comodidad." />
            </div>
        </div>
    </template>
</div>
