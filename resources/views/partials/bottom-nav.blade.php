@php
    $navCalendario   = request()->routeIs('calendario-cursos.*');
    $navCursos       = request()->routeIs('cursos.*') || request()->routeIs('modulos.*');
    $navCertificados = request()->routeIs('mis-certificados.*');
    $navSoporte      = request()->routeIs('support.*');
@endphp

<nav id="app-bottom-nav"
     aria-label="Navegación inferior"
     class="fixed bottom-0 inset-x-0 z-50 h-16 border-t border-white/10 bg-Alumco-blue px-2
            lg:hidden">

    <div class="grid h-full grid-cols-4 items-center gap-1">
        <a href="{{ route('cursos.index') }}"
           wire:navigate.hover
           class="worker-focus sidebar-cozy-btn group relative flex h-16 flex-col items-center justify-center gap-1 px-2 text-center
                  {{ $navCursos ? 'sidebar-cozy-btn-active' : 'sidebar-cozy-btn-inactive' }}"
           aria-current="{{ $navCursos ? 'page' : 'false' }}">
            <span class="flex h-6 w-6 items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.967 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.967 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                </svg>
            </span>
            <span class="text-[11px] font-black">Mis capacitaciones</span>
        </a>

        <a href="{{ route('calendario-cursos.index') }}"
           wire:navigate.hover
           class="worker-focus sidebar-cozy-btn group relative flex h-16 flex-col items-center justify-center gap-1 px-2 text-center
                  {{ $navCalendario ? 'sidebar-cozy-btn-active' : 'sidebar-cozy-btn-inactive' }}"
           aria-current="{{ $navCalendario ? 'page' : 'false' }}">
            <span class="flex h-6 w-6 items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-6h.008v.008H9v-.008ZM9 15h.008v.008H9V15Zm0 3h.008v.008H9V18Zm6-6h.008v.008H15v-.008ZM15 15h.008v.008H15V15Zm0 3h.008v.008H15V18Z" />
                </svg>
            </span>
            <span class="text-[11px] font-black">Calendario</span>
        </a>

        <a href="{{ route('mis-certificados.index') }}"
           wire:navigate.hover
           class="worker-focus sidebar-cozy-btn group relative flex h-16 flex-col items-center justify-center gap-1 px-2 text-center
                  {{ $navCertificados ? 'sidebar-cozy-btn-active' : 'sidebar-cozy-btn-inactive' }}"
           aria-current="{{ $navCertificados ? 'page' : 'false' }}">
            <span class="flex h-6 w-6 items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </span>
            <span class="text-[11px] font-black">Certificados</span>
        </a>

        <a href="{{ route('support.index') }}"
           wire:navigate.hover
           class="worker-focus sidebar-cozy-btn group relative flex h-16 flex-col items-center justify-center gap-1 px-2 text-center
                  {{ $navSoporte ? 'sidebar-cozy-btn-active' : 'sidebar-cozy-btn-inactive' }}"
           aria-current="{{ $navSoporte ? 'page' : 'false' }}">
            <span class="flex h-6 w-6 items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 10c0 3.866-3.582 7-8 7a8.84 8.84 0 0 1-4-.9L2 17l1.1-3.3A6.3 6.3 0 0 1 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7Z" />
                </svg>
            </span>
            <span class="text-[11px] font-black">Soporte</span>
        </a>
    </div>
</nav>
