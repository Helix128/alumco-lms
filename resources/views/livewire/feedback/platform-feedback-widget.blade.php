<div class="fixed bottom-24 right-4 z-[55] lg:bottom-6 lg:right-6"
     x-data
     @keydown.escape.window="$wire.set('open', false)">
    @if ($open)
        <div class="mb-3 w-[min(22rem,calc(100vw-2rem))] rounded-3xl border border-gray-100 bg-white p-5 shadow-2xl shadow-Alumco-blue/10">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-display text-lg font-black text-Alumco-blue">Feedback de plataforma</h3>
                    <p class="text-xs font-semibold text-Alumco-gray/55">Reporta problemas o sugerencias del LMS.</p>
                </div>
                <button type="button" wire:click="$set('open', false)" class="text-Alumco-gray/40 hover:text-Alumco-coral-accessible">
                    <span class="sr-only">Cerrar</span>
                    ×
                </button>
            </div>

            <form wire:submit="guardar" class="space-y-3">
                <label for="pfb-categoria" class="sr-only">Categoría del feedback</label>
                <select id="pfb-categoria" wire:model="categoria" class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-Alumco-gray outline-none focus:border-Alumco-blue">
                    <option value="sugerencia">Sugerencia</option>
                    <option value="problema">Problema técnico</option>
                    <option value="accesibilidad">Accesibilidad</option>
                    <option value="contenido">Contenido incorrecto</option>
                </select>
                <label for="pfb-mensaje" class="sr-only">Descripción</label>
                <textarea id="pfb-mensaje" wire:model="mensaje" rows="4" maxlength="1200"
                          class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-Alumco-gray outline-none focus:border-Alumco-blue"
                          placeholder="Cuéntanos qué ocurrió o qué mejorarías..."></textarea>
                @error('mensaje') <p class="text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror

                <button type="submit" class="worker-focus w-full rounded-full bg-Alumco-blue px-5 py-3 text-sm font-black text-white data-loading:opacity-70">
                    Enviar
                </button>
            </form>
        </div>
    @endif

    @if ($estado)
        <div class="mb-3 rounded-2xl border border-Alumco-green-accessible/20 bg-white px-4 py-3 text-sm font-bold text-Alumco-green-accessible shadow-lg">
            {{ $estado }}
        </div>
    @endif

    <button type="button" wire:click="$toggle('open')"
            class="worker-focus rounded-full bg-Alumco-blue px-5 py-3 text-sm font-black text-white shadow-xl shadow-Alumco-blue/20">
        Feedback
    </button>
</div>
