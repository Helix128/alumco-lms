@php
    $completado = $modulo->estaCompletadoPor(auth()->user());
    $accesible = $modulo->estaAccesiblePara(auth()->user(), $curso);
    $tipoLabel = \App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido;
@endphp

<div class="relative">
    {{-- Punto de conexión en el Timeline --}}
    <div class="absolute -left-[33px] top-1/2 z-10 h-4 w-4 -translate-y-1/2 rounded-full border-4 border-white lg:-left-[41px]
                {{ $completado ? 'bg-Alumco-green-accessible' : ($accesible ? 'bg-Alumco-blue' : 'bg-gray-300') }}"
         aria-hidden="true"></div>

    @if ($completado || $accesible)
        <a href="{{ route('modulos.show', [$curso, $modulo]) }}"
           class="worker-focus worker-card group flex items-start gap-5 p-5 transition-all duration-200 hover:shadow-lg lg:p-6
                  {{ $completado ? 'border-Alumco-green-accessible/20 bg-white' : 'border-Alumco-blue/10 bg-white' }}">

            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full font-display text-base font-black lg:h-12 lg:w-12 lg:text-lg
                        {{ $completado ? 'bg-Alumco-green-accessible text-white' : 'bg-Alumco-blue text-white' }}">
                {{ $index }}
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider lg:text-xs
                                 {{ $completado ? 'bg-Alumco-green-accessible/10 text-Alumco-green-accessible' : 'bg-Alumco-blue/10 text-Alumco-blue' }}">
                        {{ $completado ? 'Completado' : 'Disponible' }}
                    </span>
                    <span class="text-xs font-bold capitalize text-Alumco-gray/50 lg:text-sm">{{ $tipoLabel }}</span>
                </div>
                <h4 class="mt-1.5 text-lg font-black leading-snug text-Alumco-gray group-hover:text-Alumco-blue transition-colors lg:text-xl">
                    {{ $modulo->titulo }}
                </h4>
            </div>

            <div class="mt-2 shrink-0">
                @if ($completado)
                    <svg class="h-7 w-7 text-Alumco-green-accessible" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                @else
                    <svg class="h-7 w-7 text-Alumco-blue transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                @endif
            </div>
        </a>
    @else
        <div class="worker-focus worker-card group flex cursor-pointer items-start gap-5 bg-gray-50/50 p-5 transition-all hover:bg-gray-100/80 lg:p-6"
             x-data
             @click="$dispatch('show-alert', { 
                title: 'Contenido Bloqueado', 
                message: 'Para acceder a este módulo, primero debes completar todas las actividades anteriores del curso.',
                type: 'info'
             })">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gray-200 font-display text-base font-black text-gray-400 lg:h-12 lg:w-12 lg:text-lg">
                {{ $index }}
            </div>

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-gray-200 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider text-gray-500 lg:text-xs">
                        Bloqueado
                    </span>
                    <span class="text-xs font-bold capitalize text-Alumco-gray/65 lg:text-sm">{{ $tipoLabel }}</span>
                </div>
                <h4 class="mt-1.5 text-lg font-black leading-snug text-Alumco-gray/60 lg:text-xl">
                    {{ $modulo->titulo }}
                </h4>
            </div>

            <div class="mt-2 shrink-0">
                <svg class="h-7 w-7 text-gray-300 transition-transform group-hover:scale-110" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M18 8h-1V6A5 5 0 0 0 7 6v2H6a2 2 0 00-2 2v10a2 2 0 00 2 2h12a2 2 0 00 2-2V10a2 2 0 00-2-2Zm-6 9a2 2 0 1 1 0-4 2 2 0 0 1 0 4Zm3-9H9V6a3 3 0 0 1 6 0v2Z"/>
                </svg>
            </div>
        </div>
    @endif
</div>
