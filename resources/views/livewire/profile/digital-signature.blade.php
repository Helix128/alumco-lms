<section class="worker-card overflow-hidden border-none shadow-lg shadow-Alumco-blue/5 animate-page-entry">
    <div class="border-b border-slate-50 bg-slate-50/20 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="h-2 w-2 rounded-full bg-Alumco-yellow"></div>
            <h3 class="font-display text-[18px] font-black text-Alumco-blue uppercase tracking-tight">Firma para Certificados</h3>
        </div>
        <x-saving-indicator on="saved" />
    </div>

    @if ($mensaje)
        <div class="mx-6 mt-4">
            <x-alert type="success" :message="$mensaje" class="shadow-sm border-none ring-0 py-3" />
        </div>
    @endif

    <div class="p-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_280px]">
            {{-- Upload Area - More Compact --}}
            <div class="space-y-4">
                <div class="space-y-1 ml-1">
                    <h4 class="text-[11px] font-black text-Alumco-blue uppercase tracking-widest">Cargar nueva firma</h4>
                    <p class="text-[10px] font-bold text-Alumco-gray/40 italic">Usa fondo blanco o transparente.</p>
                </div>

                <div class="relative">
                    <label for="firma-digital" 
                           @class([
                               'flex min-h-[160px] cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed transition-all group',
                               'border-Alumco-blue/5 bg-slate-50/30 hover:border-Alumco-blue/10 hover:bg-white hover:shadow-md' => !$errors->has('firma_digital'),
                               'border-Alumco-coral/10 bg-Alumco-coral/5 hover:border-Alumco-coral/20' => $errors->has('firma_digital'),
                           ])>
                        
                        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm transition-all group-hover:bg-Alumco-blue group-hover:text-white">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 0 4 4m-4-4-4 4M4 20h16" />
                            </svg>
                        </div>

                        <div class="mt-4 text-center px-4">
                            <span class="block font-display text-[11px] font-black uppercase tracking-widest text-Alumco-blue">Subir Archivo</span>
                            <span class="mt-1 block text-[10px] font-bold text-Alumco-gray/30">PNG, JPG (1MB)</span>
                        </div>
                    </label>

                    <input type="file" wire:model="firma_digital" id="firma-digital" accept=".png,.jpg,.jpeg,.webp" class="sr-only">
                    
                    <div wire:loading wire:target="firma_digital" class="absolute inset-0 z-10 flex flex-col items-center justify-center rounded-3xl bg-white/90 backdrop-blur-sm">
                        <div class="h-8 w-8 rounded-full border-2 border-Alumco-blue/10 border-t-Alumco-blue animate-spin"></div>
                    </div>
                </div>

                @error('firma_digital') 
                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-Alumco-coral/5 text-Alumco-coral-accessible text-[10px] font-bold">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Preview Area - Fixed Size 120px --}}
            <div class="space-y-4">
                <div class="space-y-1 text-center lg:text-left">
                    <h4 class="text-[11px] font-black text-Alumco-blue uppercase tracking-widest">Resultado</h4>
                </div>

                <div class="relative flex h-[160px] flex-col items-center justify-center rounded-3xl border border-slate-50 bg-slate-50/50 p-4">
                    <div class="absolute inset-0 rounded-3xl opacity-20" style="background-image: radial-gradient(circle at 1px 1px, #cbd5e1 1px, transparent 0); background-size: 16px 16px;"></div>
                    
                    <div class="relative z-10">
                        @php
                            $firmaPreviewUrl = null;
                            if ($firma_digital) {
                                try { $firmaPreviewUrl = $firma_digital->temporaryUrl(); } catch (Throwable) { $firmaPreviewUrl = null; }
                            }
                        @endphp

                        @if ($firmaPreviewUrl)
                            <div class="bg-white p-3 rounded-2xl shadow-md border-2 border-white ring-4 ring-Alumco-blue/5">
                                <img src="{{ $firmaPreviewUrl }}" class="h-[120px] w-auto object-contain mix-blend-multiply" alt="Vista previa">
                            </div>
                        @elseif ($firma_actual)
                            <div class="bg-white p-3 rounded-2xl shadow-sm border border-white">
                                <img src="{{ asset('storage/'.$firma_actual) }}" class="h-[120px] w-auto object-contain mix-blend-multiply" alt="Firma actual">
                            </div>
                        @else
                            <div class="flex h-[120px] w-32 flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-100">
                                <span class="text-[10px] font-black uppercase tracking-widest text-slate-200">Vacío</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between border-t border-slate-50 bg-slate-50/20 px-6 py-4">
        <p class="text-[10px] font-bold text-Alumco-gray/30 max-w-[240px] italic leading-tight uppercase tracking-tighter">
            Se vincula a tu identidad administrativa para certificados oficiales.
        </p>
        
        <button wire:click="guardar" wire:loading.attr="disabled" @disabled(!$firma_digital)
                class="rounded-2xl bg-Alumco-blue px-6 py-3 font-display text-[11px] font-black uppercase tracking-widest text-white shadow-lg shadow-Alumco-blue/10 transition-all hover:bg-Alumco-blue/90 active:scale-95 disabled:opacity-30 disabled:pointer-events-none">
            <span wire:loading.remove wire:target="guardar">{{ $firma_actual ? 'Actualizar' : 'Guardar' }}</span>
            <span wire:loading wire:target="guardar">...</span>
        </button>
    </div>
</section>
