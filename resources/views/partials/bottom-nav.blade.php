@php
    $navPerfil       = request()->routeIs('perfil.index');
    $navCalendario   = request()->routeIs('calendario-cursos.*');
    $navCursos       = request()->routeIs('cursos.*') || request()->routeIs('modulos.*');
    $navCertificados = request()->routeIs('mis-certificados.*');

    $initials = auth()->check()
        ? collect(explode(' ', trim(auth()->user()->name)))
            ->map(fn($w) => strtoupper($w[0] ?? ''))
            ->take(2)
            ->join('')
        : '';
@endphp

<nav id="app-bottom-nav"
     class="fixed bottom-0 inset-x-0 z-50 h-16 border-t border-white/10 bg-Alumco-blue px-2
            lg:sticky lg:top-24 lg:h-auto lg:w-[280px] lg:shrink-0 lg:rounded-[22px] lg:border-none lg:bg-white lg:p-5 lg:shadow-2xl lg:shadow-Alumco-blue/10">

    <div class="grid h-full grid-cols-3 items-center gap-1 lg:flex lg:flex-col lg:gap-2">
        <a href="{{ route('cursos.index') }}"
           class="worker-focus group relative flex h-16 flex-col items-center justify-center gap-1 rounded-2xl px-2 text-center transition-all
                  {{ $navCursos
                      ? 'bg-Alumco-blue text-white shadow-lg shadow-Alumco-blue/25 lg:bg-Alumco-blue/8 lg:text-Alumco-blue lg:shadow-none lg:ring-1 lg:ring-Alumco-blue/15'
                      : 'text-white/70 hover:bg-white/10 lg:text-Alumco-gray lg:hover:bg-Alumco-blue/5' }}
                  lg:h-auto lg:flex-row lg:justify-start lg:gap-4 lg:rounded-xl lg:px-4 lg:py-5 lg:text-left"
           aria-current="{{ $navCursos ? 'page' : 'false' }}">
            @if ($navCursos)
                <span class="hidden lg:block absolute left-0 inset-y-2 w-[3px] rounded-r-full bg-Alumco-blue" aria-hidden="true"></span>
            @endif
            <span class="flex h-9 w-9 items-center justify-center rounded-xl transition-colors
                         {{ $navCursos ? 'bg-white/20 lg:bg-Alumco-blue/15' : 'bg-white/10 lg:bg-Alumco-blue/8 lg:group-hover:bg-Alumco-blue/15' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15.5A2.5 2.5 0 0 1 17.5 21H6.25A2.25 2.25 0 0 1 4 18.75V5.5Zm2.5-.5a.5.5 0 0 0-.5.5v11.56c.1-.04.2-.06.31-.06H18V5H6.5ZM6.25 19H17.5a.5.5 0 0 0 .5-.5V18H6.25a.5.5 0 0 0 0 1Z"/>
                </svg>
            </span>
            <span class="text-[11px] font-black leading-none lg:text-[15px] lg:leading-tight lg:font-black">Mis cursos</span>
        </a>

        <a href="{{ route('calendario-cursos.index') }}"
           class="worker-focus group relative flex h-16 flex-col items-center justify-center gap-1 rounded-2xl px-2 text-center transition-all
                  {{ $navCalendario
                      ? 'bg-Alumco-blue text-white shadow-lg shadow-Alumco-blue/25 lg:bg-Alumco-blue/8 lg:text-Alumco-blue lg:shadow-none lg:ring-1 lg:ring-Alumco-blue/15'
                      : 'text-white/70 hover:bg-white/10 lg:text-Alumco-gray lg:hover:bg-Alumco-blue/5' }}
                  lg:h-auto lg:flex-row lg:justify-start lg:gap-4 lg:rounded-xl lg:px-4 lg:py-5 lg:text-left"
           aria-current="{{ $navCalendario ? 'page' : 'false' }}">
            @if ($navCalendario)
                <span class="hidden lg:block absolute left-0 inset-y-2 w-[3px] rounded-r-full bg-Alumco-blue" aria-hidden="true"></span>
            @endif
            <span class="flex h-9 w-9 items-center justify-center rounded-xl transition-colors
                         {{ $navCalendario ? 'bg-white/20 lg:bg-Alumco-blue/15' : 'bg-white/10 lg:bg-Alumco-blue/8 lg:group-hover:bg-Alumco-blue/15' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V4m8 3V4M5 10h14M6.5 21h11A2.5 2.5 0 0 0 20 18.5v-11A2.5 2.5 0 0 0 17.5 5h-11A2.5 2.5 0 0 0 4 7.5v11A2.5 2.5 0 0 0 6.5 21Z"/>
                </svg>
            </span>
            <span class="text-[11px] font-black leading-none lg:text-[15px] lg:leading-tight lg:font-black">Calendario</span>
        </a>

        <a href="{{ route('mis-certificados.index') }}"
           class="worker-focus group relative flex h-16 flex-col items-center justify-center gap-1 rounded-2xl px-2 text-center transition-all
                  {{ $navCertificados
                      ? 'bg-Alumco-blue text-white shadow-lg shadow-Alumco-blue/25 lg:bg-Alumco-blue/8 lg:text-Alumco-blue lg:shadow-none lg:ring-1 lg:ring-Alumco-blue/15'
                      : 'text-white/70 hover:bg-white/10 lg:text-Alumco-gray lg:hover:bg-Alumco-blue/5' }}
                  lg:h-auto lg:flex-row lg:justify-start lg:gap-4 lg:rounded-xl lg:px-4 lg:py-5 lg:text-left"
           aria-current="{{ $navCertificados ? 'page' : 'false' }}">
            @if ($navCertificados)
                <span class="hidden lg:block absolute left-0 inset-y-2 w-[3px] rounded-r-full bg-Alumco-blue" aria-hidden="true"></span>
            @endif
            <span class="flex h-9 w-9 items-center justify-center rounded-xl transition-colors
                         {{ $navCertificados ? 'bg-white/20 lg:bg-Alumco-blue/15' : 'bg-white/10 lg:bg-Alumco-blue/8 lg:group-hover:bg-Alumco-blue/15' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 2 9.45 7.16 3.75 8l4.13 4.03-.98 5.68L12 15.03l5.1 2.68-.98-5.68L20.25 8l-5.7-.84L12 2Zm-2 18.5h4v1.5h-4v-1.5Z"/>
                </svg>
            </span>
            <span class="text-[11px] font-black leading-none lg:text-[15px] lg:leading-tight lg:font-black">Certificados</span>
        </a>
    </div>
</nav>
