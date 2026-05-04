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
                    x-on:click="if (!window.AlumcoAccessibility?.beginCooldown('font', 300)) { $event.preventDefault(); return; } window.AlumcoAccessibility?.apply({ fontLevel: 0, highContrast: document.documentElement.dataset.contrast === 'high', reducedMotion: document.documentElement.dataset.motion === 'reduced' })"
                    wire:loading.attr="disabled"
                    wire:target="setFontLevel(0)"
                    @class([
                        'flex items-center justify-center py-3 sm:py-2.5 rounded-xl text-sm font-black transition-all cursor-pointer',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 0,
                        'text-gray-500 hover:text-Alumco-gray hover:bg-white/50' => $fontLevel !== 0,
                    ])>
                Normal
            </button>
            <button type="button"
                    wire:click="setFontLevel(1)"
                    x-on:click="if (!window.AlumcoAccessibility?.beginCooldown('font', 300)) { $event.preventDefault(); return; } window.AlumcoAccessibility?.apply({ fontLevel: 1, highContrast: document.documentElement.dataset.contrast === 'high', reducedMotion: document.documentElement.dataset.motion === 'reduced' })"
                    wire:loading.attr="disabled"
                    wire:target="setFontLevel(1)"
                    @class([
                        'flex items-center justify-center py-3 sm:py-2.5 rounded-xl text-sm font-black transition-all cursor-pointer',
                        'bg-white text-Alumco-blue shadow-sm ring-1 ring-black/5' => $fontLevel === 1,
                        'text-gray-500 hover:text-Alumco-gray hover:bg-white/50' => $fontLevel !== 1,
                    ])>
                Grande
            </button>
            <button type="button"
                    wire:click="setFontLevel(2)"
                    x-on:click="if (!window.AlumcoAccessibility?.beginCooldown('font', 300)) { $event.preventDefault(); return; } window.AlumcoAccessibility?.apply({ fontLevel: 2, highContrast: document.documentElement.dataset.contrast === 'high', reducedMotion: document.documentElement.dataset.motion === 'reduced' })"
                    wire:loading.attr="disabled"
                    wire:target="setFontLevel(2)"
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
            <label
                wire:ignore
                x-data="{
                    checked: @js($highContrast),
                    busy: false,
                    cooldown: 300,
                    toggle() {
                        if (this.busy) {
                            return;
                        }

                        this.busy = true;
                        const previous = this.checked;
                        this.checked = !this.checked;

                        this.$wire.toggleHighContrast()
                            .then(() => {
                                this.checked = Boolean(this.$wire.highContrast);
                                const current = window.AlumcoAccessibility?.current() || {};

                                window.AlumcoAccessibility?.apply({
                                    fontLevel: current.fontLevel,
                                    highContrast: this.checked,
                                    reducedMotion: current.reducedMotion,
                                });
                            })
                            .catch(() => {
                                this.checked = previous;
                            })
                            .finally(() => {
                                setTimeout(() => { this.busy = false; }, this.cooldown);
                            });
                    },
                    sync(event) {
                        const preferences = window.AlumcoAccessibility?.fromEvent(event);
                        this.checked = Boolean(preferences?.highContrast);
                    },
                }"
                x-on:accessibility-preferences-updated.window="sync($event)"
                x-on:click.prevent.stop="toggle()"
                class="group relative inline-flex items-center select-none"
                :class="busy ? 'cursor-wait' : 'cursor-pointer'"
            >
                <input type="checkbox"
                       class="sr-only"
                       :checked="checked"
                       :aria-checked="checked ? 'true' : 'false'"
                       x-on:keydown.space.prevent.stop="toggle()"
                       x-on:keydown.enter.prevent.stop="toggle()"
                       aria-label="Alternar alto contraste">
                <span
                    class="relative inline-flex h-7 w-13 items-center rounded-full border transition-[background-color,border-color,box-shadow,transform] duration-200 ease-out group-active:scale-95"
                    :class="checked ? 'border-Alumco-blue bg-Alumco-blue shadow-sm shadow-Alumco-blue/20' : 'border-gray-200 bg-gray-200 shadow-inner'"
                >
                    <span
                        class="absolute left-1 h-5 w-5 rounded-full border bg-white shadow-sm transition-[transform,border-color,box-shadow] duration-200 ease-out"
                        :class="checked ? 'translate-x-6 border-white shadow-md shadow-Alumco-blue/25' : 'translate-x-0 border-gray-300'"
                    ></span>
                    <span
                        class="absolute inset-0 rounded-full ring-0 ring-Alumco-blue/20 transition-[box-shadow] duration-200"
                        :class="busy ? 'shadow-[0_0_0_4px_rgba(32,80,153,0.10)]' : ''"
                    ></span>
                </span>
            </label>
        </div>

        {{-- Toggle: Reducir Movimiento --}}
        <div class="flex items-center justify-between gap-4 p-4 rounded-2xl bg-gray-50/50 ring-1 ring-gray-100">
            <div class="flex flex-col">
                <span class="text-base font-black text-Alumco-gray">Reducir movimiento</span>
                <span class="text-xs font-semibold text-Alumco-gray/50">Desactiva transiciones y animaciones.</span>
            </div>
            <label
                wire:ignore
                x-data="{
                    checked: @js($reducedMotion),
                    busy: false,
                    cooldown: 300,
                    toggle() {
                        if (this.busy) {
                            return;
                        }

                        this.busy = true;
                        const previous = this.checked;
                        this.checked = !this.checked;

                        this.$wire.toggleReducedMotion()
                            .then(() => {
                                this.checked = Boolean(this.$wire.reducedMotion);
                                const current = window.AlumcoAccessibility?.current() || {};

                                window.AlumcoAccessibility?.apply({
                                    fontLevel: current.fontLevel,
                                    highContrast: current.highContrast,
                                    reducedMotion: this.checked,
                                });
                            })
                            .catch(() => {
                                this.checked = previous;
                            })
                            .finally(() => {
                                setTimeout(() => { this.busy = false; }, this.cooldown);
                            });
                    },
                    sync(event) {
                        const preferences = window.AlumcoAccessibility?.fromEvent(event);
                        this.checked = Boolean(preferences?.reducedMotion);
                    },
                }"
                x-on:accessibility-preferences-updated.window="sync($event)"
                x-on:click.prevent.stop="toggle()"
                class="group relative inline-flex items-center select-none"
                :class="busy ? 'cursor-wait' : 'cursor-pointer'"
            >
                <input type="checkbox"
                       class="sr-only"
                       :checked="checked"
                       :aria-checked="checked ? 'true' : 'false'"
                       x-on:keydown.space.prevent.stop="toggle()"
                       x-on:keydown.enter.prevent.stop="toggle()"
                       aria-label="Alternar reducir movimiento">
                <span
                    class="relative inline-flex h-7 w-13 items-center rounded-full border transition-[background-color,border-color,box-shadow,transform] duration-200 ease-out group-active:scale-95"
                    :class="checked ? 'border-Alumco-blue bg-Alumco-blue shadow-sm shadow-Alumco-blue/20' : 'border-gray-200 bg-gray-200 shadow-inner'"
                >
                    <span
                        class="absolute left-1 h-5 w-5 rounded-full border bg-white shadow-sm transition-[transform,border-color,box-shadow] duration-200 ease-out"
                        :class="checked ? 'translate-x-6 border-white shadow-md shadow-Alumco-blue/25' : 'translate-x-0 border-gray-300'"
                    ></span>
                    <span
                        class="absolute inset-0 rounded-full ring-0 ring-Alumco-blue/20 transition-[box-shadow] duration-200"
                        :class="busy ? 'shadow-[0_0_0_4px_rgba(32,80,153,0.10)]' : ''"
                    ></span>
                </span>
            </label>
        </div>
    </div>
</div>
