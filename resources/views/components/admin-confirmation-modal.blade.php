@props([
    'show' => false,
    'title' => '',
    'description' => '',
    'confirmLabel' => 'Confirmar',
    'confirmAction' => 'confirmPendingAction',
    'cancelAction' => 'closeConfirmation',
    'tone' => 'primary',
])

@if ($show)
    <template x-teleport="body">
        <div x-data="{ open: true }"
             x-show="open"
             x-trap.noscroll="open"
             x-on:keydown.escape.window="$wire.call('{{ $cancelAction }}')"
             class="fixed inset-0 z-[100] flex items-center justify-center p-4" 
             role="dialog" 
             aria-modal="true" 
             aria-labelledby="admin-confirmation-title">
            
            {{-- Backdrop con blur total --}}
            <div x-show="open"
                 x-transition.opacity.duration.300ms
                 class="fixed inset-0 bg-Alumco-gray/60 backdrop-blur-md" 
                 wire:click="{{ $cancelAction }}"
                 aria-hidden="true"></div>

            {{-- Contenedor del Modal (Centrado) --}}
            <div x-show="open"
                 x-transition.scale.origin.center.duration.300ms
                 class="relative w-full max-w-lg transform overflow-hidden rounded-3xl border border-white/20 bg-white p-8 shadow-2xl">
                <div class="flex items-start justify-between gap-6">
                    <div class="flex-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.25em] text-Alumco-blue/60">Confirmación requerida</p>
                        <h3 id="admin-confirmation-title" class="mt-2 font-display text-2xl font-black text-Alumco-blue leading-tight">
                            {{ $title }}
                        </h3>
                    </div>
                    <button type="button" 
                            wire:click="{{ $cancelAction }}" 
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-50 text-gray-400 transition hover:bg-red-50 hover:text-red-500" 
                            aria-label="Cerrar modal">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5">
                    <p class="text-base font-semibold leading-relaxed text-gray-600">
                        {{ $description }}
                    </p>
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" 
                            wire:click="{{ $cancelAction }}" 
                            class="rounded-2xl border border-gray-200 bg-white px-6 py-3 text-sm font-black text-gray-600 transition hover:border-gray-300 hover:bg-gray-50">
                        Cancelar operación
                    </button>
                    <button type="button" 
                            wire:click="{{ $confirmAction }}" 
                            class="inline-flex items-center justify-center rounded-2xl px-6 py-3 text-sm font-black text-white shadow-xl transition {{ $tone === 'danger' ? 'bg-Alumco-coral-accessible shadow-Alumco-coral/20 hover:bg-Alumco-coral' : 'bg-Alumco-blue shadow-Alumco-blue/20 hover:bg-Alumco-blue/90' }}">
                        {{ $confirmLabel }}
                    </button>
                </div>
            </div>
        </div>
    </template>
@endif
