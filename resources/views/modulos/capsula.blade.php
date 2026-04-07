@extends('layouts.user')

@section('title', $modulo->titulo . ' — Alumco')

@section('course-banner')
    <div class="relative overflow-hidden">
        @if ($curso->imagen_portada)
            <div class="absolute inset-0 bg-cover bg-center blur-sm scale-105"
                 style="background-image: url('{{ asset('storage/' . $curso->imagen_portada) }}')"></div>
            <div class="absolute inset-0 bg-black/20"></div>
        @endif

        <div class="relative z-10 bg-Alumco-coral/95 px-5 pt-2 pb-3 text-white text-center">
            {{-- Back link al curso --}}
            <a href="{{ route('cursos.show', $curso) }}"
               class="back-link inline-flex items-center gap-1 text-white/75 text-xs font-semibold mb-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                     stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ \Illuminate\Support\Str::limit($curso->titulo, 32) }}
            </a>
            <p class="font-bold text-base leading-tight">{{ $modulo->titulo }}</p>
            <div class="flex items-center justify-center gap-2 mt-1.5 max-w-xs mx-auto">
                <span class="text-sm font-black shrink-0">{{ $progreso }}%</span>
                <div class="flex-1 bg-white/30 rounded-full h-2.5">
                    <div class="h-2.5 bg-Alumco-green-vivid rounded-full transition-all duration-500"
                         style="width: {{ $progreso }}%"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

    {{-- Badge de tipo + contador + duración --}}
    <div class="flex items-center gap-2.5 mb-3 flex-wrap">
        <span class="bg-Alumco-blue/10 text-Alumco-blue text-xs font-bold px-3 py-1 rounded-full capitalize">
            {{ \App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido }}
        </span>
        <span class="text-Alumco-gray/50 text-xs font-medium">
            {{ $moduloActual }} de {{ $totalModulos }}
        </span>
        @if ($modulo->duracion_minutos)
            <span class="text-Alumco-gray/50 text-xs font-medium">
                · {{ $modulo->duracion_minutos }} min
            </span>
        @endif
    </div>

    <h1 class="font-display font-black text-Alumco-gray text-2xl leading-tight mb-5">
        {{ $modulo->titulo }}
    </h1>

    {{-- CONTENIDO SEGÚN TIPO --}}
    <div>

        {{-- VIDEO --}}
        @if ($modulo->tipo_contenido === 'video')
            @php
                $url = $modulo->ruta_archivo ?? '';
                $esYoutube = str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be');

                if ($esYoutube) {
                    if (str_contains($url, 'watch?v=')) {
                        $videoId = explode('v=', $url)[1];
                        $videoId = explode('&', $videoId)[0];
                        $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $videoId;
                    } elseif (str_contains($url, 'youtu.be/')) {
                        $videoId = explode('youtu.be/', $url)[1];
                        $videoId = explode('?', $videoId)[0];
                        $embedUrl = 'https://www.youtube-nocookie.com/embed/' . $videoId;
                    } else {
                        $embedUrl = $url;
                    }
                }
            @endphp

            @if ($esYoutube)
                <div class="relative w-full rounded-2xl overflow-hidden shadow-md" style="aspect-ratio: 16/9">
                    <iframe src="{{ $embedUrl }}"
                            class="absolute inset-0 w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                </div>
            @else
                <video src="{{ asset('storage/' . $url) }}"
                       controls
                       class="w-full rounded-2xl shadow-md max-h-72 lg:max-h-[500px]"
                       preload="metadata">
                    Tu navegador no soporta la reproducción de video.
                </video>
            @endif

        {{-- PDF --}}
        @elseif ($modulo->tipo_contenido === 'pdf')
            <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                <iframe src="{{ asset('storage/' . $modulo->ruta_archivo) }}"
                        class="w-full h-80 lg:h-[600px]"
                        type="application/pdf">
                    <p class="p-4 text-center text-sm text-Alumco-gray">
                        Tu navegador no puede mostrar el PDF.
                        <a href="{{ asset('storage/' . $modulo->ruta_archivo) }}"
                           target="_blank"
                           class="text-Alumco-blue font-semibold underline ml-1">Descargar PDF</a>
                    </p>
                </iframe>
            </div>

        {{-- IMAGEN --}}
        @elseif ($modulo->tipo_contenido === 'imagen')
            <img src="{{ asset('storage/' . $modulo->ruta_archivo) }}"
                 alt="{{ $modulo->titulo }}"
                 class="w-full rounded-2xl shadow-md object-contain max-h-96 lg:max-h-[600px]">

        {{-- TEXTO ENRIQUECIDO --}}
        @elseif ($modulo->tipo_contenido === 'texto')
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100
                        prose prose-sm max-w-none text-Alumco-gray
                        prose-headings:text-Alumco-gray prose-a:text-Alumco-blue">
                {!! $modulo->contenido !!}
            </div>
        @endif

    </div>

    {{-- DOS BOTONES: siguiente módulo + volver al curso --}}
    <div class="mt-8 flex flex-col gap-3 lg:items-center">

        {{-- ¡Listo! (label contextual según si hay siguiente) --}}
        <form action="{{ route('modulos.completar', [$curso, $modulo]) }}" method="POST"
              class="w-full lg:max-w-sm">
            @csrf
            <input type="hidden" name="action" value="next">
            <button type="submit"
                    class="btn-primary w-full bg-Alumco-green-vivid text-white text-xl font-black
                           py-4 rounded-2xl shadow-md">
                @if ($siguiente)
                    ¡Listo! Siguiente
                @else
                    ¡Listo! Finalizar curso
                @endif
            </button>
        </form>

        {{-- Volver al curso --}}
        <form action="{{ route('modulos.completar', [$curso, $modulo]) }}" method="POST"
              class="w-full lg:max-w-sm">
            @csrf
            <input type="hidden" name="action" value="course">
            <button type="submit"
                    class="btn-secondary w-full bg-white border-2 border-Alumco-blue/30 text-Alumco-blue
                           font-bold py-3 rounded-2xl">
                Volver al curso
            </button>
        </form>

    </div>

@endsection
