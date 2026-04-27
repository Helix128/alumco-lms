@extends('layouts.panel')

@section('title', 'Usuarios')
@section('header_title', 'Directorio de usuarios')

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
</style>
@section('header_title', 'Directorio de usuarios')

@section('content')
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
        <h2 class="text-xl font-display font-bold text-Alumco-blue/70">Gestión de Colaboradores</h2>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-4">
        <!-- Barra de búsqueda -->
        <form action="{{ route('admin.usuarios.index') }}" method="GET" class="relative w-full sm:w-72">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Buscar por nombre o correo..." 
                   class="w-full pl-11 pr-4 py-2.5 bg-white border border-gray-200 rounded-2xl shadow-sm focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all text-sm">
            <svg class="w-5 h-5 text-gray-400 absolute left-4 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </form>
        
        <!-- Botón Nuevo -->
        <button type="button" onclick="openDrawer('create')" 
                class="w-full sm:w-auto bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-bold py-2.5 px-6 rounded-2xl shadow-lg shadow-Alumco-blue/20 flex items-center justify-center transition-all active:scale-95">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo usuario
        </button>
    </div>
</div>

<!-- Tabla Card-Style -->
<div class="bg-white rounded-[24px] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Colaborador</th>
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100">Rol & Sede</th>
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-center">Estado</th>
                    <th class="px-8 py-5 text-[11px] font-display font-bold uppercase tracking-widest text-gray-400 border-b border-gray-100 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($usuarios as $user)
                <tr class="hover:bg-Alumco-cream/30 transition-colors user-row group cursor-default" 
                    data-user-id="{{ $user->id }}" 
                    data-user-name="{{ $user->name }}" 
                    data-user-email="{{ $user->email }}" 
                    data-user-role="{{ $user->roles->first()?->name }}" 
                    data-user-estamento-id="{{ $user->estamento_id }}" 
                    data-user-sede-id="{{ $user->sede_id }}" 
                    data-user-sexo="{{ $user->sexo }}" 
                    data-user-fecha-nacimiento="{{ $user->fecha_nacimiento }}" 
                    data-user-firma="{{ $user->firma_digital }}"
                    data-is-dev="{{ $user->isDesarrollador() ? 'true' : 'false' }}">
                    
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-Alumco-blue/5 text-Alumco-blue flex items-center justify-center font-display font-bold text-sm">
                                {{ collect(explode(' ', $user->name))->map(fn($n) => $n[0])->take(2)->join('') }}
                            </div>
                            <div>
                                <p class="font-display font-bold text-Alumco-gray leading-tight">{{ $user->name }}</p>
                                <p class="text-xs text-Alumco-gray/50 mt-1">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    
                    <td class="px-8 py-5">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex w-fit items-center px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider bg-Alumco-blue/10 text-Alumco-blue">
                                {{ $user->roles->first()?->name ?? 'Sin Rol' }}
                            </span>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-Alumco-gray leading-tight">
                                    {{ $user->estamento->nombre ?? 'Sin Estamento' }}
                                </span>
                                <span class="text-[11px] text-Alumco-gray/40 flex items-center gap-1 mt-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $user->sede->nombre ?? 'Sin Sede' }}
                                </span>
                            </div>
                        </div>
                    </td>
                    
                    <td class="px-8 py-5 text-center">
                        @if($user->activo)
                            <span class="badge-status bg-green-100 text-green-700">Activo</span>
                        @else
                            <span class="badge-status bg-red-100 text-red-700">Inactivo</span>
                        @endif
                    </td>
                    
                    <td class="px-8 py-5 text-right">
                        <button type="button" class="info-btn w-10 h-10 rounded-xl hover:bg-white hover:shadow-md text-gray-400 hover:text-Alumco-blue transition-all flex items-center justify-center border border-transparent hover:border-gray-100 mx-auto mr-0" title="Más opciones">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
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

<!-- Forms invisibles -->
<form id="form-toggle-status" method="POST" class="hidden">@csrf @method('PATCH')</form>
<form id="form-delete-user" method="POST" class="hidden">@csrf @method('DELETE')</form>
<form id="form-reset-password" method="POST" class="hidden">@csrf @method('PATCH')</form>

<!-- DRAWER: BACKDROP -->
<div id="drawer-backdrop" aria-hidden="true"></div>

<!-- DRAWER: PANEL LATERAL -->
<div id="drawer-usuario" aria-hidden="true">
    <!-- Header -->
    <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between shrink-0">
        <div>
            <h3 class="text-xl font-display font-black text-Alumco-blue" id="drawer-title">Nuevo usuario</h3>
            <p class="text-xs text-Alumco-gray/50 font-bold uppercase tracking-wider mt-1">Directorio de usuarios</p>
        </div>
        <button type="button" onclick="closeDrawer()" class="w-10 h-10 rounded-full hover:bg-gray-100 text-gray-400 transition-colors flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Form -->
    <form id="form-usuario" method="POST" action="{{ route('admin.usuarios.store') }}" class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">

        <!-- Scrollable Body -->
        <div class="flex-1 overflow-y-auto px-8 py-8 space-y-8 custom-scrollbar">
            
            <!-- Datos de Acceso -->
            <div class="space-y-5">
                <h4 class="text-[11px] font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Datos de Acceso</h4>
                
                <div class="space-y-1">
                    <label for="input-name" class="drawer-field-label">Nombre Completo <span class="text-Alumco-coral">*</span></label>
                    <input type="text" name="name" id="input-name" required class="drawer-input" placeholder="Ej: Juan Pérez">
                </div>

                <div class="space-y-1">
                    <label for="input-email" class="drawer-field-label">Correo Electrónico <span class="text-Alumco-coral">*</span></label>
                    <input type="email" name="email" id="input-email" required class="drawer-input" placeholder="usuario@alumco.cl">
                </div>

                <div id="div-email-notice" class="p-4 rounded-2xl bg-Alumco-blue/5 border border-Alumco-blue/10 flex gap-3">
                    <svg class="w-5 h-5 text-Alumco-blue shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-xs text-Alumco-blue/80 leading-relaxed font-medium">Se enviará un correo automático para la configuración de la contraseña.</p>
                </div>
            </div>

            <!-- Asignación -->
            <div class="space-y-5">
                <h4 class="text-[11px] font-display font-black uppercase tracking-[0.2em] text-Alumco-blue/40">Sistema y Organización</h4>
                
                <div class="grid grid-cols-1 gap-5">
                    <div class="space-y-1">
                        <label for="input-role" class="drawer-field-label">Rol en el Sistema <span class="text-Alumco-coral">*</span></label>
                        <select name="role" id="input-role" required class="drawer-select">
                            <option value="">Seleccionar...</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="input-estamento" class="drawer-field-label">Estamento / Área</label>
                        <select name="estamento_id" id="input-estamento" class="drawer-select">
                            <option value="">Ninguno / No aplica</option>                            @foreach($estamentos as $est)
                                <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="input-sede" class="drawer-field-label">Sede Asociada <span class="text-Alumco-coral">*</span></label>
                        <select name="sede_id" id="input-sede" required class="drawer-select">
                            <option value="">Seleccionar...</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label for="input-firma" class="drawer-field-label">Firma Digital (Solo Capacitadores)</label>
                        <input type="file" name="firma_digital" id="input-firma" accept="image/*" class="drawer-input text-xs">
                        <p class="text-[10px] text-Alumco-gray/40 italic">PNG transparente de 300x150px recomendado.</p>
                        <div id="firma-preview-container" class="hidden mt-2 p-2 bg-gray-50 rounded-xl border border-gray-100">
                            <img id="img-firma-preview" src="" class="h-16 object-contain mix-blend-multiply mx-auto">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div class="space-y-1">
                        <label for="input-sexo" class="drawer-field-label">Sexo</label>
                        <select name="sexo" id="input-sexo" class="drawer-select">
                            <option value="">Opcional</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label for="input-fecha_nacimiento" class="drawer-field-label">Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="input-fecha_nacimiento" class="drawer-input">
                    </div>
                </div>

                <div id="admin-role-warning" class="hidden p-4 rounded-2xl bg-amber-50 border border-amber-100 flex gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7.1 12.3A1 1 0 004.06 18h15.88a1 1 0 00.87-1.84l-7.1-12.3a1 1 0 00-1.74 0z"></path></svg>
                    <p class="text-xs text-amber-800 font-medium leading-relaxed">Este rol otorga permisos de administración total.</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-6 border-t border-gray-100 bg-gray-50/50 flex gap-3 shrink-0">
            <button type="button" onclick="closeDrawer()" class="flex-1 h-12 rounded-2xl border border-gray-200 bg-white text-Alumco-gray font-display font-bold text-sm hover:bg-gray-50 transition-colors">Cancelar</button>
            <button type="submit" id="drawer-submit-btn" class="flex-1 h-12 rounded-2xl bg-Alumco-blue text-white font-display font-bold text-sm shadow-lg shadow-Alumco-blue/20 hover:opacity-90 transition-all active:scale-95">Guardar Cambios</button>
        </div>
    </form>
</div>
@endsection

@section('context-menu')
<div id="global-context-menu" class="context-menu">
    <div class="px-3 py-2 border-b border-gray-50 text-[10px] font-display font-black text-gray-400 uppercase tracking-widest" id="context-user-name">Usuario</div>
    
    <button class="context-menu-item" onclick="handleAction('edit')">
        <svg class="w-4 h-4 mr-3 text-Alumco-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
        Editar colaborador
    </button>
    <button class="context-menu-item" onclick="handleAction('status')">
        <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Cambiar estado
    </button>
    <button class="context-menu-item" onclick="handleAction('reset')">
        <svg class="w-4 h-4 mr-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        Resetear acceso
    </button>
    
    <div class="h-px bg-gray-50 my-1 mx-2"></div>
    
    <button class="context-menu-item text-red-600 hover:bg-red-50" onclick="handleAction('delete')">
        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        Eliminar registro
    </button>
</div>
@endsection

@push('scripts')
<script>
    let activeUserId = null;

    const routeTemplates = {
        toggleStatus: @json(route('admin.usuarios.toggle-status', ['user' => '__USER__'])),
        resetPassword: @json(route('admin.usuarios.reset-password', ['user' => '__USER__'])),
        destroy: @json(route('admin.usuarios.destroy', ['user' => '__USER__'])),
        update: @json(route('admin.usuarios.update', ['user' => '__USER__'])),
    };

    const buildRoute = (template, userId) => template.replace('__USER__', userId);

    // --- Context menu ---
    document.addEventListener('DOMContentLoaded', () => {
        const rows = document.querySelectorAll('.user-row');
        const contextMenu = document.getElementById('global-context-menu');
        const contextUserName = document.getElementById('context-user-name');

        const showMenu = (e, targetRow, btnRect = null) => {
            e.preventDefault();

            const isTargetDev = targetRow.dataset.isDev === 'true';
            const iAmDev = {{ auth()->user()->isDesarrollador() ? 'true' : 'false' }};
            if (isTargetDev && !iAmDev) return;

            activeUserId = targetRow.dataset.userId;
            contextUserName.textContent = targetRow.dataset.userName;
            contextMenu.classList.add('active');

            let x, y;
            if (btnRect) {
                x = btnRect.left - contextMenu.offsetWidth + btnRect.width;
                y = btnRect.bottom + 5;
            } else {
                x = e.clientX;
                y = e.clientY;
            }

            if (x + contextMenu.offsetWidth > window.innerWidth)
                x = window.innerWidth - contextMenu.offsetWidth - 10;
            if (y + contextMenu.offsetHeight > window.innerHeight)
                y = window.innerHeight - contextMenu.offsetHeight - 10;

            contextMenu.style.left = `${x}px`;
            contextMenu.style.top = `${y}px`;
        };

        rows.forEach(row => {
            row.addEventListener('contextmenu', (e) => showMenu(e, row));
        });

        document.querySelectorAll('.info-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const row = btn.closest('.user-row');
                showMenu(e, row, btn.getBoundingClientRect());
            });
        });

        document.addEventListener('click', () => contextMenu.classList.remove('active'));
    });

    function handleAction(action) {
        if (!activeUserId) return;

        switch (action) {
            case 'edit':
                openDrawer('edit', activeUserId);
                break;
            case 'status':
                if (confirm('\u00bfSeguro que deseas cambiar el estado de este usuario?')) {
                    const f = document.getElementById('form-toggle-status');
                    f.action = buildRoute(routeTemplates.toggleStatus, activeUserId);
                    f.submit();
                }
                break;
            case 'reset':
                if (confirm('\u00bfEnviar correo de recuperaci\u00f3n de contrase\u00f1a a este usuario?')) {
                    const f = document.getElementById('form-reset-password');
                    f.action = buildRoute(routeTemplates.resetPassword, activeUserId);
                    f.submit();
                }
                break;
            case 'delete':
                if (confirm('A punto de eliminar usuario. \u00bfContinuar?')) {
                    const f = document.getElementById('form-delete-user');
                    f.action = buildRoute(routeTemplates.destroy, activeUserId);
                    f.submit();
                }
                break;
        }
        document.getElementById('global-context-menu').classList.remove('active');
    }

    // --- Drawer ---
    (function () {
        const drawer   = document.getElementById('drawer-usuario');
        const backdrop = document.getElementById('drawer-backdrop');
        const form     = document.getElementById('form-usuario');
        const els = {
            title:    document.getElementById('drawer-title'),
            method:   document.getElementById('form-method'),
            emailNotice: document.getElementById('div-email-notice'),
            role:     document.getElementById('input-role'),
            estamento: document.getElementById('input-estamento'),
            warning:  document.getElementById('admin-role-warning'),
            firmaContainer: document.getElementById('firma-preview-container'),
            firmaImg: document.getElementById('img-firma-preview'),
            firmaInput: document.getElementById('input-firma'),
        };

        let isOpen = false;

        function updateAdminWarning() {
            if (!els.role || !els.warning) return;
            const text = (els.role.options[els.role.selectedIndex]?.value || '').trim().toLowerCase();
            const show = text === 'desarrollador' || text === 'administrador';
            els.warning.style.display = show ? 'flex' : 'none';
        }

        function open(mode, userId) {
            form.reset();
            els.firmaContainer.classList.add('hidden');
            els.firmaImg.src = '';
            els.firmaInput.value = '';

            if (mode === 'edit' && userId) {
                const row = document.querySelector('.user-row[data-user-id="' + userId + '"]');
                if (!row) return;

                form.action        = buildRoute(routeTemplates.update, userId);
                els.method.value   = 'PUT';
                els.title.textContent = 'Editar Colaborador';
                els.emailNotice.style.display = 'none';

                document.getElementById('input-name').value  = row.dataset.userName || '';
                document.getElementById('input-email').value = row.dataset.userEmail || '';
                els.role.value = row.dataset.userRole || '';
                els.estamento.value = row.dataset.userEstamentoId || '';
                document.getElementById('input-sede').value  = row.dataset.userSedeId || '';
                if (row.dataset.userSexo)
                    document.getElementById('input-sexo').value = row.dataset.userSexo;
                if (row.dataset.userFechaNacimiento)
                    document.getElementById('input-fecha_nacimiento').value = row.dataset.userFechaNacimiento;
                
                if (row.dataset.userFirma) {
                    els.firmaImg.src = '/storage/' + row.dataset.userFirma;
                    els.firmaContainer.classList.remove('hidden');
                }
            } else {
                form.action        = "{{ route('admin.usuarios.store') }}";
                els.method.value   = 'POST';
                els.title.textContent = 'Nuevo Usuario';
                els.emailNotice.style.display = 'flex';
            }

            updateAdminWarning();

            drawer.setAttribute('aria-hidden', 'false');
            backdrop.setAttribute('aria-hidden', 'false');
            void drawer.offsetHeight; 
            drawer.classList.add('is-open');
            backdrop.classList.add('is-open');
            isOpen = true;
        }

        function close() {
            if (!isOpen) return;
            isOpen = false;
            drawer.classList.remove('is-open');
            backdrop.classList.remove('is-open');
            setTimeout(() => {
                drawer.setAttribute('aria-hidden', 'true');
                backdrop.setAttribute('aria-hidden', 'true');
            }, 400);
        }

        backdrop.addEventListener('click', close);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isOpen) close(); });
        if (els.role) els.role.addEventListener('change', updateAdminWarning);

        window.openDrawer  = open;
        window.closeDrawer = close;
    })();
</script>
@endpush
