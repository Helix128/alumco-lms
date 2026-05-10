<div class="space-y-8 animate-page-entry">
    {{-- Header Section --}}
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="space-y-1">
            <h2 class="admin-page-title">Gestión de Estamentos</h2>
            <p class="admin-page-subtitle max-w-2xl">
                Segmentación laboral de colaboradoras y colaboradores para la asignación estratégica de capacitaciones y contenidos.
            </p>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
            <x-saving-indicator on="saved" />
            <button 
                type="button" 
                wire:click="openCreate" 
                @class([
                    'admin-action-button admin-action-button--primary transition-polish active-press',
                    'opacity-50 pointer-events-none' => $showForm && !$editingId
                ])
                wire:loading.attr="disabled"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>Nuevo estamento</span>
            </button>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if (session()->has('success'))
        <x-alert type="success" :message="session('success')" class="shadow-sm" />
    @endif

    @if (session()->has('error'))
        <x-alert type="error" :message="session('error')" class="shadow-sm" />
    @endif

    {{-- Stats Summary --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="admin-surface p-5 flex flex-col justify-between">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue/50">Total Catálogo</p>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="font-display text-4xl font-black text-Alumco-blue">{{ $estamentos->count() }}</span>
                <span class="text-xs font-bold text-Alumco-gray/60">Categorías</span>
            </div>
        </div>

        <div class="admin-surface p-5 flex flex-col justify-between">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue/50">Cobertura Total</p>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="font-display text-4xl font-black text-Alumco-blue">{{ number_format($estamentos->sum('users_count')) }}</span>
                <span class="text-xs font-bold text-Alumco-gray/60">Colaboradores</span>
            </div>
        </div>

        <div class="admin-surface p-5 flex flex-col justify-between">
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-Alumco-blue/50">Capacitaciones distribuidas</p>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="font-display text-4xl font-black text-Alumco-blue">{{ $estamentos->sum('cursos_count') }}</span>
                <span class="text-xs font-bold text-Alumco-gray/60">Asignaciones</span>
            </div>
        </div>
    </section>

    {{-- Upsert Form --}}
    @if ($showForm)
        <section class="admin-surface p-6 ring-2 ring-Alumco-blue/5 animate-page-entry overflow-hidden relative">
            <div class="absolute top-0 left-0 w-1 h-full bg-Alumco-blue"></div>
            
            <div class="mb-6">
                <h3 class="font-display text-lg font-black text-Alumco-blue">
                    {{ $editingId ? 'Actualizar Estamento' : 'Nuevo Estamento' }}
                </h3>
                <p class="text-xs font-bold text-Alumco-gray/60 uppercase tracking-widest mt-1">
                    {{ $editingId ? 'Editando registro existente' : 'Configuración de nueva categoría' }}
                </p>
            </div>

            <form wire:submit="save" class="grid gap-6 lg:grid-cols-[1fr_auto] items-end">
                <div class="space-y-2">
                    <label for="estamento-nombre" class="text-[11px] font-black text-Alumco-gray/70 uppercase tracking-widest ml-1">
                        Nombre del estamento
                    </label>
                    <div class="relative group">
                        <input
                            id="estamento-nombre"
                            type="text"
                            wire:model="nombre"
                            @class([
                                'w-full rounded-xl border bg-slate-50 px-5 py-4 text-sm font-bold text-Alumco-blue transition-polish focus:bg-white focus:outline-none focus:ring-4',
                                'border-slate-200 focus:border-Alumco-blue focus:ring-Alumco-blue/10' => !$errors->has('nombre'),
                                'border-Alumco-coral focus:border-Alumco-coral focus:ring-Alumco-coral/10' => $errors->has('nombre'),
                            ])
                            placeholder="Ej: Técnicos de Enfermería"
                            autocomplete="off"
                            autofocus
                        >
                    </div>
                    @error('nombre')
                        <p class="text-xs font-bold text-Alumco-coral-accessible flex items-center gap-1.5 ml-1">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="cancel" class="admin-action-button admin-action-button--ghost justify-center flex-1 sm:flex-none">
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="admin-action-button admin-action-button--primary justify-center flex-1 sm:flex-none min-w-[140px]"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove wire:target="save">
                            {{ $editingId ? 'Guardar cambios' : 'Crear estamento' }}
                        </span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Procesando...
                        </span>
                    </button>
                </div>
            </form>
        </section>
    @endif

    {{-- Main Inventory Table --}}
    <section class="admin-surface overflow-hidden border-none shadow-xl shadow-Alumco-blue/5">
        <div class="border-b border-slate-100 bg-white/50 px-6 py-5 flex items-center justify-between">
            <div class="space-y-0.5">
                <p class="text-[10px] font-black uppercase tracking-[0.25em] text-Alumco-blue/40">Inventario</p>
                <h3 class="font-display text-xl font-black text-Alumco-blue">Estamentos configurados</h3>
            </div>
            
            <div wire:loading wire:target="deleteEstamento, edit" class="flex items-center gap-2 text-[10px] font-black text-Alumco-blue/40 uppercase tracking-widest">
                <svg class="animate-spin h-3.5 w-3.5 text-Alumco-blue" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Actualizando...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px] text-left">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-Alumco-gray/50 border-b border-slate-100">Estamento</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-Alumco-gray/50 border-b border-slate-100">Colaboradores</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-Alumco-gray/50 border-b border-slate-100">Capacitaciones asociadas</th>
                        <th class="px-6 py-4 text-[11px] font-black uppercase tracking-widest text-Alumco-gray/50 border-b border-slate-100 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50 bg-white">
                    @forelse ($estamentos as $estamento)
                        @php
                            $isInUse = $estamento->users_count > 0 || $estamento->cursos_count > 0;
                            $isEditing = $editingId === $estamento->id;
                        @endphp
                        <tr 
                            wire:key="estamento-row-{{ $estamento->id }}" 
                            @class([
                                'group transition-colors',
                                'bg-Alumco-blue/5' => $isEditing,
                                'hover:bg-slate-50/80' => !$isEditing
                            ])
                        >
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    @if ($isEditing)
                                        <div class="h-2 w-2 rounded-full bg-Alumco-blue animate-pulse"></div>
                                    @endif
                                    <span @class([
                                        'font-display text-base font-black',
                                        'text-Alumco-blue' => $isEditing,
                                        'text-Alumco-gray' => !$isEditing
                                    ])>
                                        {{ $estamento->nombre }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-Alumco-gray">{{ number_format($estamento->users_count) }}</span>
                                    <span class="text-[10px] font-black text-Alumco-gray/30 uppercase tracking-tighter">Personas</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-Alumco-gray">{{ number_format($estamento->cursos_count) }}</span>
                                    <span class="text-[10px] font-black text-Alumco-gray/30 uppercase tracking-tighter">Capacitaciones</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button 
                                        type="button" 
                                        wire:click="edit({{ $estamento->id }})" 
                                        class="admin-action-button admin-action-button--ghost admin-action-button--small hover:bg-Alumco-blue hover:text-white hover:border-Alumco-blue"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        <span>Editar</span>
                                    </button>

                                    @if (!$isInUse)
                                        <button
                                            type="button"
                                            wire:click="deleteEstamento({{ $estamento->id }})"
                                            wire:confirm="¿Estás seguro de eliminar el estamento '{{ $estamento->nombre }}'? Esta acción es irreversible."
                                            class="admin-action-button admin-action-button--danger admin-action-button--small hover:bg-Alumco-coral-accessible hover:text-white"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <span>Eliminar</span>
                                        </button>
                                    @else
                                        <div class="group/tooltip relative">
                                            <div class="cursor-not-allowed opacity-30 admin-action-button admin-action-button--ghost admin-action-button--small border-dashed">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                <span>Bloqueado</span>
                                            </div>
                                            <div class="absolute bottom-full right-0 mb-2 w-48 scale-0 group-hover/tooltip:scale-100 transition-all origin-bottom-right bg-Alumco-blue text-[10px] font-bold text-white p-3 rounded-xl shadow-xl z-20 leading-tight">
                                                No se puede eliminar porque tiene personal o capacitaciones asociadas.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-20">
                                <div class="flex flex-col items-center justify-center text-center space-y-4">
                                    <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center">
                                        <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <p class="font-display text-lg font-black text-Alumco-blue">Catálogo vacío</p>
                                        <p class="text-sm font-medium text-Alumco-gray/50 max-w-xs mx-auto">
                                            Aún no has configurado estamentos laborales. Crea el primero para comenzar a segmentar tu personal.
                                        </p>
                                    </div>
                                    <button type="button" wire:click="openCreate" class="admin-action-button admin-action-button--secondary mt-4">
                                        Crear primer estamento
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-slate-50/50 px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[10px] font-black uppercase tracking-widest text-Alumco-gray/40">
                Página 1 de 1
            </p>
            <p class="text-[10px] font-black uppercase tracking-widest text-Alumco-gray/40">
                Alumco LMS &copy; {{ date('Y') }}
            </p>
        </div>
    </section>
</div>
