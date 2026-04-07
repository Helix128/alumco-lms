@php
    $navPerfil       = request()->routeIs('perfil.index');
    $navCursos       = request()->routeIs('cursos.*') || request()->routeIs('modulos.*');
    $navCertificados = request()->routeIs('mis-certificados.*');
@endphp

<nav class="fixed bottom-0 inset-x-0 z-50 bg-Alumco-blue h-16 flex items-center justify-around
            lg:static lg:border-t lg:border-white/10 lg:h-14">

    {{-- Perfil --}}
    <a href="{{ route('perfil.index') }}"
       class="nav-item flex flex-col items-center gap-0.5 px-6
              {{ $navPerfil ? 'text-white' : 'text-white/60' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/>
        </svg>
        <span class="text-[10px] font-semibold hidden lg:inline">Perfil</span>
        @if ($navPerfil)
            <span class="block w-1.5 h-1.5 rounded-full bg-white mx-auto lg:hidden"></span>
        @endif
    </a>

    {{-- Mis Cursos (elevado, circular) --}}
    <a href="{{ route('cursos.index') }}"
       class="nav-courses-btn relative -mt-8 bg-Alumco-blue border-4 rounded-full p-3.5 shadow-lg text-white
              {{ $navCursos ? 'border-Alumco-green' : 'border-Alumco-cream' }}
              lg:mt-0 lg:border-0 lg:rounded-lg lg:px-6 lg:py-2 lg:flex lg:items-center lg:gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
        </svg>
        <span class="text-xs hidden lg:inline font-semibold">Mis cursos</span>
    </a>

    {{-- Mis Logros --}}
    <a href="{{ route('mis-certificados.index') }}"
       class="nav-item flex flex-col items-center gap-0.5 px-6
              {{ $navCertificados ? 'text-white' : 'text-white/60' }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 1L9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1z"/>
        </svg>
        <span class="text-[10px] font-semibold hidden lg:inline">Mis logros</span>
        @if ($navCertificados)
            <span class="block w-1.5 h-1.5 rounded-full bg-white mx-auto lg:hidden"></span>
        @endif
    </a>

</nav>
