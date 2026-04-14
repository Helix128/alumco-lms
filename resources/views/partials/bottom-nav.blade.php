@php
    $navPerfil       = request()->routeIs('perfil.index');
    $navCalendario   = request()->routeIs('calendario-cursos.*');
    $navCursos       = request()->routeIs('cursos.*') || request()->routeIs('modulos.*');
    $navCertificados = request()->routeIs('mis-certificados.*');
@endphp

<nav id="app-bottom-nav" class="fixed bottom-0 inset-x-0 z-50 bg-Alumco-blue h-16 flex items-center justify-around
            lg:sticky lg:top-24 lg:w-64 lg:h-auto lg:flex-col lg:justify-start lg:gap-2 lg:bg-white lg:border lg:border-gray-100 lg:rounded-3xl lg:p-3 lg:shadow-sm lg:mt-6 lg:shrink-0 lg:z-10">

    {{-- Perfil --}}
    <a href="{{ route('perfil.index') }}"
       class="nav-item flex flex-col items-center gap-0.5 px-4
              {{ $navPerfil ? 'text-white lg:bg-Alumco-blue' : 'text-white/60 lg:text-Alumco-gray lg:hover:bg-gray-50' }}
              lg:flex-row lg:w-full lg:px-5 lg:py-3.5 lg:rounded-2xl lg:shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 lg:w-6 lg:h-6 lg:shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
        </svg>
        <span class="text-[10px] font-semibold hidden lg:inline lg:text-[15px] lg:font-bold lg:ml-3">Perfil</span>
        @if ($navPerfil)
            <span class="block w-1.5 h-1.5 rounded-full bg-white mx-auto lg:hidden"></span>
        @endif
    </a>

    {{-- Calendario --}}
    <a href="{{ route('calendario-cursos.index') }}"
       class="nav-item flex flex-col items-center gap-0.5 px-4
              {{ $navCalendario ? 'text-white lg:bg-Alumco-blue' : 'text-white/60 lg:text-Alumco-gray lg:hover:bg-gray-50' }}
              lg:flex-row lg:w-full lg:px-5 lg:py-3.5 lg:rounded-2xl lg:shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 lg:w-6 lg:h-6 lg:shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span class="text-[10px] font-semibold hidden lg:inline lg:text-[15px] lg:font-bold lg:ml-3">Calendario</span>
        @if ($navCalendario)
            <span class="block w-1.5 h-1.5 rounded-full bg-white mx-auto lg:hidden"></span>
        @endif
    </a>

    {{-- Mis Cursos (elevado, circular) --}}
    <a href="{{ route('cursos.index') }}"
       class="nav-courses-btn relative -mt-8 bg-Alumco-blue border-4 rounded-full p-3.5 shadow-lg text-white
              {{ $navCursos ? 'border-Alumco-green lg:bg-Alumco-blue lg:text-white' : 'border-Alumco-cream lg:text-Alumco-gray lg:bg-transparent lg:hover:bg-gray-50' }}
              lg:mt-0 lg:border-0 lg:rounded-2xl lg:shadow-sm lg:px-5 lg:py-3.5 lg:flex lg:flex-row lg:w-full lg:items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 lg:w-6 lg:h-6 lg:shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
        <span class="text-xs hidden lg:inline lg:text-[15px] lg:font-bold lg:ml-3">Mis cursos</span>
    </a>

    {{-- Mis Logros --}}
    <a href="{{ route('mis-certificados.index') }}"
       class="nav-item flex flex-col items-center gap-0.5 px-4
              {{ $navCertificados ? 'text-white lg:bg-Alumco-blue' : 'text-white/60 lg:text-Alumco-gray lg:hover:bg-gray-50' }}
              lg:flex-row lg:w-full lg:px-5 lg:py-3.5 lg:rounded-2xl lg:shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 lg:w-6 lg:h-6 lg:shrink-0" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 1L9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1z"/>
        </svg>
        <span class="text-[10px] font-semibold hidden lg:inline lg:text-[15px] lg:font-bold lg:ml-3">Mis logros</span>
        @if ($navCertificados)
            <span class="block w-1.5 h-1.5 rounded-full bg-white mx-auto lg:hidden"></span>
        @endif
    </a>

</nav>
