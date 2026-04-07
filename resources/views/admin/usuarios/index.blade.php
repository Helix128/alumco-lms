@extends('layouts.panel')

@section('title', 'Panel de administración')

@push('styles')
<style>
    /* --- Drawer: estados y transiciones --- */
    #drawer-backdrop {
        position: fixed;
        top: 0;
        left: 80px;              /* ancho del sidebar */
        right: 0;
        bottom: 0;
        z-index: 30;
        background: rgba(74, 74, 74, 0.40);
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
        top: var(--header-h, 0px);
        right: 0;
        z-index: 40;
        height: calc(100% - var(--header-h, 0px));
        width: 420px;
        display: flex;
        flex-direction: column;
        background: #fff;
        box-shadow: -4px 0 24px rgba(0, 0, 0, 0.15);
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #drawer-usuario.is-open {
        transform: translateX(0);
    }

    /* --- Drawer: inputs --- */
    #drawer-usuario .drawer-input,
    #drawer-usuario .drawer-select {
        height: 40px;
        border: 1px solid rgba(74, 74, 74, 0.28);
        border-radius: 8px;
        background: #fff;
        color: #4A4A4A;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    #drawer-usuario .drawer-input:focus,
    #drawer-usuario .drawer-select:focus {
        outline: none;
        border-color: #205099;
        box-shadow: 0 0 0 3px rgba(32, 80, 153, 0.18);
    }

    #drawer-usuario .drawer-input::placeholder {
        color: rgba(74, 74, 74, 0.4);
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="flex justify-between items-end mb-8">
    <div>
        <h2 class="text-[32px] font-bold text-Alumco-gray mb-1">Directorio de Usuarios</h2>
        <p class="text-sm text-Alumco-gray/70">Haz clic derecho en un usuario (o usa el ícono) para ver sus opciones.</p>
    </div>
    
    <div class="flex items-center gap-4">
        <!-- Barra de búsqueda -->
        <form action="{{ route('admin.usuarios.index') }}" method="GET" class="flex items-center gap-3">
            <svg class="w-6 h-6 text-Alumco-gray/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar nombre o correo..." 
                   class="px-4 py-2 border border-Alumco-gray/30 rounded-Alumco focus-ring text-sm w-64 text-Alumco-gray">
        </form>
        
        <!-- Botón Nuevo -->
        <button type="button" onclick="openDrawer('create')" class="bg-Alumco-blue hover:bg-opacity-90 text-white font-bold py-2 px-4 rounded-Alumco shadow flex items-center transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo usuario
        </button>
    </div>
</div>

<div class="bg-white rounded-Alumco border border-Alumco-gray/20 overflow-hidden shadow-sm">
    <table class="min-w-full leading-normal text-Alumco-gray">
        <thead>
            <tr>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Nombre</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Rol/Estamento</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Sede</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-left text-sm font-bold tracking-wider">Estado</th>
                <th class="px-5 py-3 border-b border-Alumco-gray/20 bg-white text-right text-sm font-bold tracking-wider">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($usuarios as $user)
            <tr class="hover:bg-Alumco-cream/50 transition-colors user-row cursor-context-menu" 
                data-user-id="{{ $user->id }}" 
                data-user-name="{{ $user->name }}" 
                data-user-email="{{ $user->email }}" 
                data-user-estamento-id="{{ $user->estamento_id }}" 
                data-user-sede-id="{{ $user->sede_id }}" 
                data-user-sexo="{{ $user->sexo }}" 
                data-user-fecha-nacimiento="{{ $user->fecha_nacimiento }}" 
                data-is-dev="{{ $user->isDesarrollador() ? 'true' : 'false' }}">
                
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                    <p class="font-bold whitespace-no-wrap">{{ $user->name }}</p>
                    <p class="text-xs opacity-75 mt-0.5">{{ $user->email }}</p>
                </td>
                
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm font-medium">
                    @if($user->isDesarrollador())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-purple-100 text-purple-800">
                            {{ $user->estamento->nombre }}
                        </span>
                    @elseif($user->isAdmin())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                            {{ $user->estamento->nombre }}
                        </span>
                    @else
                        {{ $user->estamento->nombre ?? 'N/A' }}
                    @endif
                </td>
                
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm font-medium">
                    <p class="whitespace-no-wrap">{{ $user->sede->nombre ?? 'N/A' }}</p>
                </td>
                
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm">
                    @if($user->activo)
                    <span class="text-green-600 font-bold">Activo</span>
                    @else
                    <span class="text-red-600 font-bold">Inactivo</span>
                    @endif
                </td>
                
                <td class="px-5 py-4 border-b border-Alumco-gray/10 bg-transparent text-sm text-right">
                    <button type="button" class="info-btn p-2 rounded-full hover:bg-Alumco-gray/10 text-Alumco-gray transition-colors focus:outline-none" title="Opciones (o Click Derecho)">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-8 border-b border-Alumco-gray/10 bg-transparent text-sm text-center opacity-75">
                    No se encontraron usuarios.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-3 bg-white border-t border-Alumco-gray/10 flex items-center justify-between">
        {{ $usuarios->links() }}
    </div>
</div>

<!-- FORMS INVISIBLES PARA ACCIONES RÁPIDAS -->
<form id="form-toggle-status" method="POST" class="hidden">
    @csrf
    @method('PATCH')
</form>
<form id="form-delete-user" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="form-reset-password" method="POST" class="hidden">
    @csrf
    @method('PATCH')
</form>

<!-- DRAWER: BACKDROP -->
<div id="drawer-backdrop" aria-hidden="true"></div>

<!-- DRAWER: PANEL LATERAL -->
<div id="drawer-usuario" aria-hidden="true">

    <!-- Header -->
    <div class="bg-Alumco-blue px-5 py-4 flex items-center justify-between gap-3 shrink-0">
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/50 mb-0.5"></p>
            <h3 class="text-xl font-black text-white leading-tight truncate" id="drawer-title">Nuevo usuario</h3>
        </div>
        <button type="button" onclick="closeDrawer()" aria-label="Cerrar panel" class="shrink-0 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 rounded-full p-1.5 transition-colors focus:outline-none">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Form -->
    <form id="form-usuario" method="POST" action="{{ route('admin.usuarios.store') }}" class="flex flex-col flex-1 min-h-0">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">

        <!-- Scrollable body -->
        <div class="px-5 py-5 space-y-5 overflow-y-auto custom-scrollbar flex-1">

            <!-- Required fields note -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide border border-Alumco-blue/20 bg-Alumco-cyan/20 text-Alumco-blue">Obligatorios</span>
                <p class="text-[12px] text-Alumco-gray/55">Los campos con <span class="text-Alumco-coral font-bold">*</span> son requeridos.</p>
            </div>

            <!-- Seccion: Datos de acceso -->
            <fieldset class="space-y-3">
                <legend class="text-[11px] font-extrabold uppercase tracking-widest text-Alumco-blue">Datos de acceso</legend>

                <div>
                    <label for="input-name" class="block text-sm font-bold text-Alumco-gray mb-1">Nombre completo <span class="text-Alumco-coral">*</span></label>
                    <input type="text" name="name" id="input-name" required class="drawer-input w-full px-3 text-sm" placeholder="Ej: María Fernanda González">
                </div>

                <div>
                    <label for="input-email" class="block text-sm font-bold text-Alumco-gray mb-1">Correo electrónico <span class="text-Alumco-coral">*</span></label>
                    <input type="email" name="email" id="input-email" required class="drawer-input w-full px-3 text-sm" placeholder="usuario@alumco.cl">
                </div>

                <div id="div-email-notice" class="rounded-Alumco border border-Alumco-blue/20 bg-Alumco-cyan/10 px-3 py-2 text-[13px] text-Alumco-blue">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Se enviará un correo automático al usuario para que configure su contraseña.</span>
                    </div>
                </div>
            </fieldset>

            <!-- Seccion: Asignacion organizacional -->
            <fieldset class="space-y-3">
                <legend class="text-[11px] font-extrabold uppercase tracking-widest text-Alumco-blue">Asignación organizacional</legend>

                <div>
                    <label for="input-estamento" class="block text-sm font-bold text-Alumco-gray mb-1">Rol / Estamento <span class="text-Alumco-coral">*</span></label>
                    <div class="relative">
                        <select name="estamento_id" id="input-estamento" required class="drawer-select w-full px-3 pr-9 text-sm appearance-none">
                            <option value="">Seleccionar...</option>
                            @foreach($estamentos as $est)
                                <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-Alumco-gray/50 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div>
                    <label for="input-sede" class="block text-sm font-bold text-Alumco-gray mb-1">Sede asociada <span class="text-Alumco-coral">*</span></label>
                    <div class="relative">
                        <select name="sede_id" id="input-sede" required class="drawer-select w-full px-3 pr-9 text-sm appearance-none">
                            <option value="">Seleccionar...</option>
                            @foreach($sedes as $sede)
                                <option value="{{ $sede->id }}">{{ $sede->nombre }}</option>
                            @endforeach
                        </select>
                        <svg class="w-4 h-4 text-Alumco-gray/50 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div>
                    <label for="input-sexo" class="block text-sm font-bold text-Alumco-gray mb-1">Sexo <span class="text-[12px] font-normal text-Alumco-gray/50">(opcional)</span></label>
                    <div class="relative">
                        <select name="sexo" id="input-sexo" class="drawer-select w-full px-3 pr-9 text-sm appearance-none">
                            <option value="">No especificar</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <svg class="w-4 h-4 text-Alumco-gray/50 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div>
                    <label for="input-fecha_nacimiento" class="block text-sm font-bold text-Alumco-gray mb-1">Fecha de nacimiento <span class="text-[12px] font-normal text-Alumco-gray/50">(opcional)</span></label>
                    <input type="date" name="fecha_nacimiento" id="input-fecha_nacimiento" class="drawer-input w-full px-3 text-sm">
                </div>

                <div id="admin-role-warning" class="hidden rounded-Alumco border border-amber-300 bg-amber-50 px-3 py-2 text-[13px] text-amber-900">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86l-7.1 12.3A1 1 0 004.06 18h15.88a1 1 0 00.87-1.84l-7.1-12.3a1 1 0 00-1.74 0z"></path></svg>
                        <span>Este rol tiene privilegios administrativos. Asígnalo solo a personal autorizado.</span>
                    </div>
                </div>
            </fieldset>
        </div>

        <!-- Footer -->
        <div class="px-5 py-3.5 border-t border-Alumco-gray/15 bg-Alumco-cream flex justify-end gap-2 shrink-0">
            <button type="button" onclick="closeDrawer()" class="h-9 px-4 rounded-Alumco border border-Alumco-gray/25 bg-white text-Alumco-gray font-bold text-sm hover:bg-Alumco-gray/5 transition-colors">Cancelar</button>
            <button type="submit" id="drawer-submit-btn" class="h-9 px-5 rounded-Alumco bg-Alumco-blue text-white font-bold text-sm shadow-sm hover:opacity-90 transition-opacity">Guardar usuario</button>
        </div>
    </form>
</div>
@endsection

@section('context-menu')
<div id="global-context-menu" class="context-menu">
    <div class="px-4 py-2 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider" id="context-user-name">Usuario</div>
    
    <button class="context-menu-item flex items-center gap-2 text-Alumco-blue font-medium" onclick="handleAction('edit')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
        Editar datos
    </button>
    <button class="context-menu-item flex items-center gap-2 font-medium" onclick="handleAction('status')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Cambiar estado (Activo/Inactivo)
    </button>
    <button class="context-menu-item flex items-center gap-2 font-medium" onclick="handleAction('reset')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        Resetear contraseña
    </button>
    
    <div class="h-px bg-gray-200 my-1"></div>
    
    <button class="context-menu-item flex items-center gap-2 text-red-600 font-bold hover:bg-red-50" onclick="handleAction('delete')">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        Eliminar usuario
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
        // Medir header real y setear CSS custom property
        var header = document.querySelector('header');
        if (header) {
            document.documentElement.style.setProperty('--header-h', header.offsetHeight + 'px');
        }

        const drawer   = document.getElementById('drawer-usuario');
        const backdrop = document.getElementById('drawer-backdrop');
        const form     = document.getElementById('form-usuario');
        const els = {
            title:    document.getElementById('drawer-title'),
            method:   document.getElementById('form-method'),
            emailNotice: document.getElementById('div-email-notice'),
            estamento: document.getElementById('input-estamento'),
            warning:  document.getElementById('admin-role-warning'),
        };

        let isOpen = false;

        /* -- Helpers -- */
        function updateAdminWarning() {
            if (!els.estamento || !els.warning) return;
            const text = (els.estamento.options[els.estamento.selectedIndex]?.text || '').trim().toLowerCase();
            const show = text === 'desarrollador' || text === 'administrador';
            els.warning.style.display = show ? '' : 'none';
        }

        /* -- Abrir drawer -- */
        function open(mode, userId) {
            form.reset();

            if (mode === 'edit' && userId) {
                const row = document.querySelector('.user-row[data-user-id="' + userId + '"]');
                if (!row) return;

                form.action        = buildRoute(routeTemplates.update, userId);
                els.method.value   = 'PUT';
                els.title.textContent = 'Editar usuario';
                els.emailNotice.style.display = 'none';

                document.getElementById('input-name').value  = row.dataset.userName || '';
                document.getElementById('input-email').value = row.dataset.userEmail || '';
                els.estamento.value = row.dataset.userEstamentoId || '';
                document.getElementById('input-sede').value  = row.dataset.userSedeId || '';
                if (row.dataset.userSexo)
                    document.getElementById('input-sexo').value = row.dataset.userSexo;
                if (row.dataset.userFechaNacimiento)
                    document.getElementById('input-fecha_nacimiento').value = row.dataset.userFechaNacimiento;
            } else {
                form.action        = "{{ route('admin.usuarios.store') }}";
                els.method.value   = 'POST';
                els.title.textContent = 'Nuevo usuario';
                els.emailNotice.style.display = '';
            }

            updateAdminWarning();

            // Mostrar + forzar reflow antes de animar
            drawer.setAttribute('aria-hidden', 'false');
            backdrop.setAttribute('aria-hidden', 'false');
            void drawer.offsetHeight;               // reflow
            drawer.classList.add('is-open');
            backdrop.classList.add('is-open');
            isOpen = true;

            // Focus al primer input tras la animacion
            drawer.addEventListener('transitionend', function onEnd() {
                drawer.removeEventListener('transitionend', onEnd);
                var first = form.querySelector('input:not([type=hidden]):not([style*="display: none"]):not([style*="display:none"])');
                if (first) first.focus();
            });
        }

        /* -- Cerrar drawer -- */
        function close() {
            if (!isOpen) return;
            isOpen = false;

            drawer.classList.remove('is-open');
            backdrop.classList.remove('is-open');

            drawer.addEventListener('transitionend', function onEnd() {
                drawer.removeEventListener('transitionend', onEnd);
                drawer.setAttribute('aria-hidden', 'true');
                backdrop.setAttribute('aria-hidden', 'true');
            });
        }

        /* -- Bind events -- */
        backdrop.addEventListener('click', close);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) close();
        });

        if (els.estamento) {
            els.estamento.addEventListener('change', updateAdminWarning);
        }

        /* -- Exponer al scope global (las llaman el onclick del HTML y handleAction) -- */
        window.openDrawer  = open;
        window.closeDrawer = close;
    })();
</script>
@endpush