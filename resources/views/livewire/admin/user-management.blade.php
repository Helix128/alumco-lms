<div x-data>
    @push('styles')
    <style>
        /* Drawer: estados y transiciones */
        #drawer-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
            background: rgba(32, 80, 153, 0.2);
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        #drawer-backdrop.is-open {
            opacity: 1;
            pointer-events: auto;
        }

        #drawer-usuario {
            position: fixed;
            top: 0;
            right: 0;
            z-index: 50;
            height: 100%;
            width: 100%;
            max-width: 480px;
            display: flex;
            flex-direction: column;
            background: #fff;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.05);
            transform: translateX(100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #drawer-usuario.is-open {
            transform: translateX(0);
        }

        /* Estilos de inputs en el drawer */
        .drawer-field-label {
            display: block;
            font-family: 'Sora', sans-serif;
            font-size: 0.813rem;
            font-weight: 700;
            color: var(--color-Alumco-gray);
            margin-bottom: 0.375rem;
        }

        .drawer-input, .drawer-select {
            width: 100%;
            height: 44px;
            padding: 0 1rem;
            background-color: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 10px;
            font-size: 0.875rem;
            color: var(--color-Alumco-gray);
            transition: all 0.2s;
        }

        .drawer-input:focus, .drawer-select:focus {
            outline: none;
            background-color: #fff;
            border-color: var(--color-Alumco-blue);
            box-shadow: 0 0 0 4px rgba(32, 80, 153, 0.1);
        }

        .badge-status {
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            z-index: 30;
            min-width: 180px;
            background: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            padding: 0.5rem;
        }
        
        .dropdown-menu.is-open {
            display: block;
        }
    </style>
    @endpush

    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-xl font-display font-bold text-Alumco-blue/70">Gestión de Colaboradores</h2>
        </div>

        <div class="flex flex-col sm:flex-row items-center gap-4">
            <!-- Barra de búsqueda -->
            <div class="relative w-full sm:w-72">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nombre o correo..." 
                       class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-2xl shadow-sm focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all text-sm">
                <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            
            <!-- Botón Nuevo -->
            <button type="button" wire:click="openCreate" 
                    class="w-full sm:w-auto bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-bold py-2.5 px-6 rounded-2xl shadow-lg shadow-Alumco-blue/20 flex items-center justify-center transition-all active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo usuario
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p class="font-bold text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            <p class="font-bold text-sm">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Tabla Card-Style -->
    <div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Colaborador</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Email</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">RUT</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Rol</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Estamento</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Sede</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-center">Estado</th>
                        <th class="px-6 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($usuarios as $user)
                    <tr class="hover:bg-Alumco-cream/30 transition-colors user-row group cursor-default">
                        
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center font-display font-bold text-xs shrink-0">
                                    {{ collect(explode(' ', $user->name))->map(fn($n) => $n[0])->take(2)->join('') }}
                                </div>
                                <p class="font-display font-bold text-Alumco-gray leading-tight text-sm">{{ $user->name }}</p>
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <span class="text-sm text-Alumco-gray font-medium">{{ $user->email }}</span>
                        </td>

                        <td class="px-6 py-5">
                            <span class="text-xs font-bold text-Alumco-blue/60 uppercase tracking-tight">{{ $user->rut ?? '—' }}</span>
                        </td>
                        
                        <td class="px-6 py-5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-Alumco-blue/10 text-Alumco-blue border border-Alumco-blue/5">
                                {{ $user->roles->first()?->name ?? 'Sin Rol' }}
                            </span>
                        </td>

                        <td class="px-6 py-5">
                            <span class="text-sm font-bold text-Alumco-gray">
                                {{ $user->estamento->nombre ?? '—' }}
                            </span>
                        </td>

                        <td class="px-6 py-5">
                            <div class="flex items-center gap-1.5 text-Alumco-gray/60">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-sm font-medium">{{ $user->sede->nombre ?? '—' }}</span>
                            </div>
                        </td>
                        
                        <td class="px-6 py-5 text-center">
                            @if($user->activo)
                                <span class="badge-status bg-green-100 text-green-700">Activo</span>
                            @else
                                <span class="badge-status bg-red-100 text-red-700">Inactivo</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-5 text-right relative" x-data="{ open: false }">
                            @php
                                $canManage = auth()->user()->canManageUser($user);
                            @endphp

                            @if($canManage)
                            <button type="button" @click="open = !open" @click.away="open = false"
                                    class="w-10 h-10 rounded-xl hover:bg-white hover:shadow-md text-gray-400 hover:text-Alumco-blue transition-all flex items-center justify-center border border-transparent hover:border-gray-100 ml-auto" title="Más opciones">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-8 top-12 w-48 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 text-left">
                                
                                <button wire:click="edit({{ $user->id }})" class="w-full px-4 py-2 text-xs font-bold text-Alumco-gray hover:bg-Alumco-blue/5 hover:text-Alumco-blue flex items-center gap-3 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    Editar colaborador
                                </button>
                                
                                <button wire:click="toggleStatus({{ $user->id }})" wire:confirm="\u00bfSeguro que deseas cambiar el estado de este usuario?" class="w-full px-4 py-2 text-xs font-bold text-Alumco-gray hover:bg-Alumco-blue/5 hover:text-green-600 flex items-center gap-3 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Cambiar estado
                                </button>

                                <button wire:click="resetPassword({{ $user->id }})" wire:confirm="\u00bfEnviar correo de recuperaci\u00f3n de contrase\u00f1a a este usuario?" class="w-full px-4 py-2 text-xs font-bold text-Alumco-gray hover:bg-Alumco-blue/5 hover:text-amber-600 flex items-center gap-3 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    Resetear acceso
                                </button>

                                <div class="h-px bg-gray-100 my-1 mx-2"></div>

                                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="A punto de eliminar usuario. \u00bfContinuar?" class="w-full px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Eliminar registro
                                </button>
                            </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-12 text-center">
                            <div class="flex flex-col items-center opacity-40">
                                <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="font-display font-bold">No se encontraron usuarios</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usuarios->hasPages())
        <div class="px-8 py-5 border-t border-gray-50 bg-gray-50/30">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>

    <!-- DRAWER: BACKDROP -->
    <div id="drawer-backdrop" :class="{ 'is-open': $wire.showDrawer }" aria-hidden="true" @click="$wire.showDrawer = false"></div>

    <!-- DRAWER: PANEL LATERAL -->
    <div id="drawer-usuario" :class="{ 'is-open': $wire.showDrawer }" aria-hidden="true">
        <!-- Header -->
        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between shrink-0">
            <div>
                <h3 class="text-xl font-display font-black text-Alumco-blue">
                    {{ $editingUser ? 'Editar usuario' : 'Nuevo usuario' }}
                </h3>
                <p class="text-xs text-Alumco-gray/50 font-bold uppercase tracking-wider mt-1">Directorio de usuarios</p>
            </div>
            <button type="button" @click="$wire.showDrawer = false" class="w-10 h-10 rounded-full hover:bg-gray-100 text-gray-400 transition-colors flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Form -->
        <form wire:submit="save" class="flex flex-col flex-1 min-h-0">
            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto px-8 py-8 space-y-8 custom-scrollbar">
                
                <!-- Datos de Acceso -->
                <div class="space-y-5">
                    <h4 class="text-[11px] font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Datos de Acceso</h4>
                    
                    <div class="space-y-1">
                        <label for="input-name" class="drawer-field-label">Nombre Completo <span class="text-Alumco-coral">*</span></label>
                        <input type="text" wire:model="name" id="input-name" required class="drawer-input" placeholder="Ej: Juan Pérez">
                        @error('name') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="input-email" class="drawer-field-label">Correo Electrónico <span class="text-Alumco-coral">*</span></label>
                        <input type="email" wire:model="email" id="input-email" required class="drawer-input" placeholder="usuario@alumco.cl">
                        @error('email') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="input-rut" class="drawer-field-label">RUT <span class="text-Alumco-gray/40 text-[10px] font-normal tracking-normal">(Ej: 12.345.678-9)</span></label>
                        <input type="text" wire:model="rut" id="input-rut" class="drawer-input uppercase" placeholder="12.345.678-9">
                        @error('rut') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                    </div>

                    @if(!$editingUser)
                    <div class="p-4 rounded-2xl bg-Alumco-blue/5 border border-Alumco-blue/10 flex gap-3">
                        <svg class="w-5 h-5 text-Alumco-blue shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs text-Alumco-blue/80 leading-relaxed font-medium">Se enviará un correo automático para la configuración de la contraseña.</p>
                    </div>
                    @endif
                </div>

                <!-- Asignación -->
                <div class="space-y-5">
                    <h4 class="text-[11px] font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Sistema y Organización</h4>
                    
                    <div class="grid grid-cols-1 gap-5">
                        <div class="space-y-1">
                            <label for="input-role" class="drawer-field-label">Rol en el Sistema <span class="text-Alumco-coral">*</span></label>
                            <select wire:model.live="role" id="input-role" required class="drawer-select">
                                <option value="">Seleccionar...</option>
                                @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="input-estamento" class="drawer-field-label">Estamento / Área</label>
                            <select wire:model="estamento_id" id="input-estamento" class="drawer-select">
                                <option value="">Ninguno / No aplica</option>
                                @foreach($estamentos as $est)
                                    <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                                @endforeach
                            </select>
                            @error('estamento_id') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="input-sede" class="drawer-field-label">Sede Asociada <span class="text-Alumco-coral">*</span></label>
                            <select wire:model="sede_id" id="input-sede" required class="drawer-select">
                                <option value="">Seleccionar...</option>
                                @foreach($sedes as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                            @error('sede_id') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>

                        <div class="space-y-1" x-show="$wire.role === 'Capacitador'" x-transition>
                            <label for="input-firma" class="drawer-field-label">Firma Digital</label>
                            <input type="file" wire:model="firma_digital" id="input-firma" accept="image/*" class="drawer-input text-xs">
                            <p class="text-[10px] text-Alumco-gray/40 italic">PNG transparente de 300x150px recomendado.</p>
                            @error('firma_digital') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                            
                            @if ($firma_digital)
                                <div class="mt-2 p-2 bg-gray-50 rounded-xl border border-gray-100">
                                    <img src="{{ $firma_digital->temporaryUrl() }}" class="h-16 object-contain mix-blend-multiply mx-auto">
                                </div>
                            @elseif ($firma_digital_url)
                                <div class="mt-2 p-2 bg-gray-50 rounded-xl border border-gray-100">
                                    <img src="{{ $firma_digital_url }}" class="h-16 object-contain mix-blend-multiply mx-auto">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-5">
                        <div class="space-y-1">
                            <label for="input-sexo" class="drawer-field-label">Sexo</label>
                            <select wire:model="sexo" id="input-sexo" class="drawer-select">
                                <option value="">Opcional</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                            @error('sexo') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1">
                            <label for="input-fecha_nacimiento" class="drawer-field-label">Nacimiento</label>
                            <input type="date" wire:model="fecha_nacimiento" id="input-fecha_nacimiento" class="drawer-input">
                            @error('fecha_nacimiento') <span class="text-xs text-red-600 font-bold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div x-show="$wire.role === 'Administrador' || $wire.role === 'Desarrollador'" x-transition 
                         class="p-4 rounded-2xl bg-amber-50 border border-amber-100 flex gap-3">
                        <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7.1 12.3A1 1 0 004.06 18h15.88a1 1 0 00.87-1.84l-7.1-12.3a1 1 0 00-1.74 0z"></path></svg>
                        <p class="text-xs text-amber-800 font-medium leading-relaxed">Este rol otorga permisos de administración total.</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/50 flex gap-3 shrink-0">
                <button type="button" @click="$wire.showDrawer = false" class="flex-1 h-12 rounded-2xl border border-gray-200 bg-white text-Alumco-gray font-display font-bold text-sm hover:bg-gray-50 transition-colors">Cancelar</button>
                <button type="submit" class="flex-1 h-12 rounded-2xl bg-Alumco-blue text-white font-display font-bold text-sm shadow-lg shadow-Alumco-blue/20 hover:opacity-90 transition-all active:scale-95">
                    <span wire:loading.remove wire:target="save, firma_digital">Guardar Cambios</span>
                    <span wire:loading wire:target="save, firma_digital">Procesando...</span>
                </button>
            </div>
        </form>
    </div>
</div>
