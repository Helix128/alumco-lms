@php
    $navPerfil       = request()->routeIs('perfil.index');
    $navCalendario   = request()->routeIs('calendario-cursos.*');
    $navCursos       = request()->routeIs('cursos.*') || request()->routeIs('modulos.*');
    $navCertificados = request()->routeIs('mis-certificados.*');
@endphp

<nav id="app-bottom-nav"
     class="fixed bottom-0 inset-x-0 z-50 h-16 border-t border-white/10 bg-Alumco-blue px-2
            lg:sticky lg:top-24 lg:h-auto lg:w-[280px] lg:shrink-0 lg:border-none lg:bg-transparent lg:p-0 lg:shadow-none">

    <div class="flex h-full flex-col">
        {{-- Etiqueta de Ayuda (Solo PC) --}}
        <div class="hidden lg:mb-5 lg:block lg:px-2">
            <h2 class="text-xs font-black uppercase tracking-widest text-Alumco-gray/40">Menú de navegación</h2>
        </div>

        {{-- Contenedor de Botones --}}
        <div class="grid h-full grid-cols-3 items-center gap-1.5 lg:flex lg:flex-col lg:gap-3">
            <a href="{{ route('cursos.index') }}"
               wire:navigate
               class="worker-focus sidebar-cozy-btn group relative flex h-16 flex-col items-center justify-center gap-1 px-2 text-center
                      {{ $navCursos ? 'sidebar-cozy-btn-active' : 'sidebar-cozy-btn-inactive' }}
                      lg:h-auto lg:w-full lg:flex-row lg:justify-start lg:gap-4 lg:rounded-2xl lg:px-5 lg:py-4 lg:text-left"
               aria-current="{{ $navCursos ? 'page' : 'false' }}">
                <span class="flex h-6 w-6 items-center justify-center lg:h-5 lg:w-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.967 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.967 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </span>
                <span class="text-[11px] font-black lg:text-[15px] lg:font-bold">Mis cursos</span>
            </a>

            <a href="{{ route('calendario-cursos.index') }}"
               wire:navigate
               class="worker-focus sidebar-cozy-btn group relative flex h-16 flex-col items-center justify-center gap-1 px-2 text-center
                      {{ $navCalendario ? 'sidebar-cozy-btn-active' : 'sidebar-cozy-btn-inactive' }}
                      lg:h-auto lg:w-full lg:flex-row lg:justify-start lg:gap-4 lg:rounded-2xl lg:px-5 lg:py-4 lg:text-left"
               aria-current="{{ $navCalendario ? 'page' : 'false' }}">
                <span class="flex h-6 w-6 items-center justify-center lg:h-5 lg:w-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-6h.008v.008H9v-.008ZM9 15h.008v.008H9V15Zm0 3h.008v.008H9V18Zm6-6h.008v.008H15v-.008ZM15 15h.008v.008H15V15Zm0 3h.008v.008H15V18Z" />
                    </svg>
                </span>
                <span class="text-[11px] font-black lg:text-[15px] lg:font-bold">Calendario</span>
            </a>

            <a href="{{ route('mis-certificados.index') }}"
               wire:navigate
               class="worker-focus sidebar-cozy-btn group relative flex h-16 flex-col items-center justify-center gap-1 px-2 text-center
                      {{ $navCertificados ? 'sidebar-cozy-btn-active' : 'sidebar-cozy-btn-inactive' }}
                      lg:h-auto lg:w-full lg:flex-row lg:justify-start lg:gap-4 lg:rounded-2xl lg:px-5 lg:py-4 lg:text-left"
               aria-current="{{ $navCertificados ? 'page' : 'false' }}">
                <span class="flex h-6 w-6 items-center justify-center lg:h-5 lg:w-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
                <span class="text-[11px] font-black lg:text-[15px] lg:font-bold">Certificados</span>
            </a>
        </div>
    </div>
</nav>
