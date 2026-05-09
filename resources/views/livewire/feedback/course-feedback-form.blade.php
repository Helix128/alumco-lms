<div>
    @if ($progreso >= 100)
        <section class="worker-card p-5 lg:p-7">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <h3 class="font-display text-2xl font-black text-Alumco-gray">Feedback del curso</h3>
                <p class="text-sm font-semibold text-Alumco-gray/60">Tu opinión ayuda a mejorar las próximas capacitaciones.</p>
            </div>
            <x-saving-indicator />
        </div>

        @if ($estado)
            <div class="mb-4 rounded-2xl border border-Alumco-green-accessible/20 bg-Alumco-green/25 px-4 py-3 text-sm font-bold text-Alumco-green-accessible">
                {{ $estado }}
            </div>
        @endif

        <form wire:submit="guardar" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-Alumco-blue/50">Valoración</label>
                    <select wire:model="rating" class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-Alumco-gray outline-none focus:border-Alumco-blue">
                        <option value="">Selecciona una nota</option>
                        <option value="5">5 · Muy útil</option>
                        <option value="4">4 · Útil</option>
                        <option value="3">3 · Aceptable</option>
                        <option value="2">2 · Mejorable</option>
                        <option value="1">1 · Deficiente</option>
                    </select>
                    @error('rating') <p class="mt-1 text-sm font-bold text-Alumco-coral-accessible">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-widest text-Alumco-blue/50">Tema principal</label>
                    <select wire:model="categoria" class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-Alumco-gray outline-none focus:border-Alumco-blue">
                        <option value="utilidad">Utilidad para el trabajo</option>
                        <option value="claridad">Claridad</option>
                        <option value="contenido">Contenido</option>
                        <option value="duracion">Duración</option>
                    </select>
                </div>
            </div>

            <textarea wire:model="mensaje" rows="3" maxlength="1200"
                      placeholder="Comentario opcional..."
                      class="worker-focus w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold text-Alumco-gray outline-none focus:border-Alumco-blue"></textarea>

            <div class="flex justify-end">
                <button type="submit" class="worker-focus rounded-full bg-Alumco-green-accessible px-6 py-3 text-sm font-black text-white shadow-sm data-loading:opacity-70">
                    Guardar feedback
                </button>
            </div>
        </form>
        </section>
    @endif
</div>
