@props(['name', 'options' => [], 'selected' => [], 'placeholder' => 'Seleccionar...'])

<div class="relative" 
     x-data="{ 
        open: false, 
        search: '', 
        selected: @js(array_map('strval', $selected)),
        options: @js($options),
        toggle(id) {
            id = String(id);
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
            }
        },
        get selectedLabels() {
            return this.selected.map(id => this.options[id]).filter(l => l);
        },
        get filteredOptions() {
            if (!this.search) return Object.entries(this.options);
            return Object.entries(this.options).filter(([id, label]) => 
                label.toLowerCase().includes(this.search.toLowerCase())
            );
        }
     }" 
     @click.away="open = false">
    
    {{-- Trigger --}}
    <button type="button" 
            @click="open = !open"
            class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 text-sm font-bold text-Alumco-gray flex items-center justify-between hover:border-Alumco-blue/30 transition-all shadow-sm">
        <span class="truncate" x-text="selected.length === 0 ? '{{ $placeholder }}' : (selected.length === 1 ? selectedLabels[0] : selected.length + ' seleccionados')"></span>
        <div class="flex items-center gap-2">
            <span class="bg-Alumco-blue/10 text-Alumco-blue text-[10px] px-1.5 py-0.5 rounded-md" x-text="selected.length"></span>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </div>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 origin-top"
         x-transition:enter-end="opacity-100 scale-100 origin-top"
         class="absolute z-30 left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 p-4">
        
        <div class="space-y-4">
            {{-- Buscador --}}
            <div class="relative">
                <input type="text" x-model="search" placeholder="Buscar..." 
                       class="w-full bg-gray-50 border border-gray-100 rounded-xl pl-10 pr-4 py-2 text-xs focus:ring-4 focus:ring-Alumco-blue/10 outline-none">
                <svg class="w-4 h-4 text-gray-300 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            {{-- Opciones --}}
            <div class="max-h-60 overflow-y-auto custom-scrollbar space-y-1">
                <template x-for="[id, label] in filteredOptions" :key="id">
                    <label class="flex items-center gap-3 p-2 rounded-xl hover:bg-Alumco-blue/5 transition-all cursor-pointer group">
                        <input type="checkbox" :name="'{{ $name }}[]'" :value="id" 
                               :checked="selected.includes(String(id))"
                               @change="toggle(id)"
                               class="w-4 h-4 rounded border-gray-300 text-Alumco-blue focus:ring-Alumco-blue/20">
                        <span class="text-xs font-bold text-Alumco-gray group-hover:text-Alumco-blue transition-colors" x-text="label"></span>
                    </label>
                </template>
                <div x-show="filteredOptions.length === 0" class="py-4 text-center text-xs text-gray-400">No hay resultados</div>
            </div>

            <div class="pt-3 border-t border-gray-50 flex justify-between">
                <button type="button" @click="selected = []" class="text-[10px] font-black uppercase text-gray-300 hover:text-Alumco-coral">Limpiar selección</button>
            </div>
        </div>
    </div>

    {{-- Chips --}}
    <div class="flex flex-wrap gap-1.5 mt-2">
        <template x-for="(label, index) in selectedLabels.slice(0, 5)" :key="index">
            <div class="inline-flex items-center gap-2 rounded-full border border-Alumco-blue/20 bg-white text-Alumco-blue text-[10px] font-black uppercase px-3 py-1.5">
                <span x-text="label"></span>
                <button type="button" @click="toggle(Object.keys(options).find(key => options[key] === label))" class="text-gray-400 hover:text-Alumco-coral">✕</button>
            </div>
        </template>
        <div x-show="selected.length > 5" class="text-[10px] font-bold text-gray-400 self-center ml-1" x-text="'+' + (selected.length - 5) + ' más'"></div>
    </div>
</div>
