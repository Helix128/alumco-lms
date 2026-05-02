@extends('layouts.user')

@section('title', $modulo->titulo . ' — Alumco')

@section('course-banner')
    <div class="border-b border-Alumco-blue/8 bg-gradient-to-br from-Alumco-blue/[0.04] via-white to-Alumco-cyan/[0.06] px-4 py-6 lg:px-12 lg:py-7">
        <div class="mx-auto max-w-[90rem]">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <a href="{{ route('cursos.show', $curso) }}"
                       class="worker-focus mb-3 inline-flex items-center gap-2 rounded-full border border-Alumco-blue/15 bg-white/80 px-4 py-2 text-sm font-bold text-Alumco-blue shadow-sm transition-colors hover:border-Alumco-blue/30 hover:bg-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                        {{ \Illuminate\Support\Str::limit($curso->titulo, 44) }}
                    </a>
                    <p class="mb-1 text-sm font-bold text-Alumco-blue/55">
                        Módulo {{ $moduloActual }} de {{ $totalModulos }}
                    </p>
                    <h1 class="font-display text-2xl font-black leading-tight tracking-tight text-Alumco-gray lg:text-3xl">
                        {{ $modulo->titulo }}
                    </h1>
                </div>

                <div class="w-full shrink-0 lg:max-w-xs">
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-Alumco-gray/60">Progreso del curso</span>
                        <span class="text-lg font-black text-Alumco-blue">{{ $progreso }}%</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-Alumco-blue/10">
                        <div class="h-full rounded-full bg-Alumco-blue transition-all duration-500"
                             style="width: {{ $progreso }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')

@php
    $tipoIconPath = match($modulo->tipo_contenido) {
        'video'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75L15.75 12 12 15.75v-7.5Z"/>',
        'pdf', 'ppt', 'descargable' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
        'imagen'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>',
        'evaluacion'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
        default       => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
    };
@endphp

<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-2.5">
        <span class="inline-flex items-center gap-1.5 rounded-full bg-Alumco-blue/10 px-4 py-2 text-sm font-black capitalize text-Alumco-blue">
            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                {!! $tipoIconPath !!}
            </svg>
            {{ \App\Models\Modulo::TIPO_LABELS[$modulo->tipo_contenido] ?? $modulo->tipo_contenido }}
        </span>
        @if ($modulo->duracion_minutos)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-4 py-2 text-sm font-bold text-Alumco-gray/70 ring-1 ring-gray-200">
                <svg class="h-4 w-4 shrink-0 text-Alumco-gray/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                {{ $modulo->duracion_minutos }} min
            </span>
        @endif
    </div>

    <section>
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
                <div class="worker-card relative w-full overflow-hidden" style="aspect-ratio: 16/9">
                    <iframe src="{{ $embedUrl }}"
                            class="absolute inset-0 h-full w-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                </div>
            @else
                <x-file-viewer :rutaArchivo="$modulo->ruta_archivo"
                               :archivoUrl="route('modulos.archivo', [$curso, $modulo], false)"
                               :descargarUrl="route('modulos.descargar', [$curso, $modulo], false)"
                               :nombreOriginal="$modulo->nombre_archivo_original" />
            @endif
        @elseif (in_array($modulo->tipo_contenido, ['pdf', 'ppt', 'imagen', 'descargable']))
            <x-file-viewer :rutaArchivo="$modulo->ruta_archivo"
                           :archivoUrl="route('modulos.archivo', [$curso, $modulo], false)"
                           :descargarUrl="route('modulos.descargar', [$curso, $modulo], false)"
                           :nombreOriginal="$modulo->nombre_archivo_original" />
        @elseif ($modulo->tipo_contenido === 'texto')
            <div class="worker-card p-5 text-Alumco-gray prose prose-base max-w-none
                        prose-headings:text-Alumco-gray prose-a:text-Alumco-blue lg:p-7">
                {!! $modulo->contenido !!}
            </div>
        @endif
    </section>

    <div class="grid gap-4 lg:max-w-2xl lg:grid-cols-2">
        <form action="{{ route('modulos.completar', [$curso, $modulo]) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="next">
            <button type="submit"
                    class="btn-primary worker-focus worker-action w-full rounded-full bg-Alumco-green-accessible px-5 py-4 text-lg font-black text-white shadow-sm">
                @if ($siguiente)
                    Listo, siguiente
                @else
                    Finalizar curso
                @endif
            </button>
        </form>

        <form action="{{ route('modulos.completar', [$curso, $modulo]) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="course">
            <button type="submit"
                    class="btn-secondary worker-focus worker-action w-full rounded-full border-2 border-Alumco-blue/35 bg-white px-5 py-4 text-lg font-black text-Alumco-blue">
                Volver al curso
            </button>
        </form>
    </div>
</div>

@endsection
