@props(['rutaArchivo', 'archivoUrl' => null, 'descargarUrl' => null, 'nombreOriginal' => null, 'displayExtension' => null, 'posterUrl' => null, 'moduloId' => null])

@php
    $extension = $displayExtension ?: ($rutaArchivo ? strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION)) : null);
    $url = $archivoUrl ?? ($rutaArchivo ? Storage::url($rutaArchivo) : null);
    $finalDownloadUrl = $descargarUrl ?? $url;

    $isPdf = $extension === 'pdf';
    $isPowerPoint = in_array($extension, ['ppt', 'pptx']);
@endphp

@if ($extension)
    @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
        <img src="{{ $url }}" 
             alt="Archivo de módulo" 
             class="worker-card w-full object-contain max-h-96 lg:max-h-[640px]"
             loading="lazy"
             decoding="async">

    @elseif (in_array($extension, ['mp4', 'webm', 'ogg']))
        <div class="space-y-3" data-video-container>
            <video src="{{ $url }}" 
                   @if ($posterUrl) poster="{{ $posterUrl }}" @endif
                   controls 
                   class="worker-card w-full max-h-72 lg:max-h-[560px] bg-black"
                   preload="metadata"
                   data-player-video
                   @if ($moduloId) data-module-id="{{ $moduloId }}" @endif>
                Tu navegador no soporta la reproducción de video.
            </video>

            <div class="flex flex-wrap items-center justify-between gap-3 px-1 text-xs text-Alumco-gray/60 font-bold">
                <div class="flex items-center gap-2">
                    <span>Velocidad:</span>
                    <div class="inline-flex rounded-lg border border-Alumco-blue/15 bg-white p-0.5 shadow-sm" data-speed-controls>
                        <button type="button" class="rounded px-2 py-1 text-xs font-bold text-Alumco-gray hover:text-Alumco-blue" data-speed="0.75">0.75x</button>
                        <button type="button" class="rounded bg-Alumco-blue/10 px-2 py-1 text-xs font-black text-Alumco-blue" data-speed="1">1x</button>
                        <button type="button" class="rounded px-2 py-1 text-xs font-bold text-Alumco-gray hover:text-Alumco-blue" data-speed="1.25">1.25x</button>
                        <button type="button" class="rounded px-2 py-1 text-xs font-bold text-Alumco-gray hover:text-Alumco-blue" data-speed="1.5">1.5x</button>
                        <button type="button" class="rounded px-2 py-1 text-xs font-bold text-Alumco-gray hover:text-Alumco-blue" data-speed="2">2x</button>
                    </div>
                </div>
                @if ($finalDownloadUrl)
                    <a href="{{ $finalDownloadUrl }}" download class="inline-flex items-center gap-1.5 text-Alumco-blue hover:underline">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Descargar video
                    </a>
                @endif
            </div>
        </div>

    @elseif ($isPdf)
        <div class="worker-card overflow-hidden bg-slate-50" data-module-pdf-viewer data-pdf-url="{{ $url }}">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-Alumco-blue/10 bg-white px-4 py-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-black text-Alumco-gray">{{ $nombreOriginal ?? 'Documento PDF' }}</p>
                    <p class="text-xs font-bold text-Alumco-gray/55" data-pdf-status></p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button"
                            class="worker-focus inline-flex h-10 w-10 items-center justify-center rounded-full bg-Alumco-blue/10 text-Alumco-blue disabled:cursor-not-allowed disabled:opacity-35"
                            data-pdf-previous
                            aria-label="Página anterior">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m15 18-6-6 6-6"/>
                        </svg>
                    </button>
                    <span class="min-w-20 text-center text-sm font-black text-Alumco-gray">
                        <span data-pdf-current-page>1</span>/<span data-pdf-total-pages>1</span>
                    </span>
                    <button type="button"
                            class="worker-focus inline-flex h-10 w-10 items-center justify-center rounded-full bg-Alumco-blue/10 text-Alumco-blue disabled:cursor-not-allowed disabled:opacity-35"
                            data-pdf-next
                            aria-label="Página siguiente">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>
                    <span class="mx-1 h-8 w-px bg-Alumco-blue/10"></span>
                    <button type="button"
                            class="worker-focus inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-Alumco-blue ring-1 ring-Alumco-blue/10"
                            data-pdf-zoom-out
                            aria-label="Reducir zoom">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                        </svg>
                    </button>
                    <button type="button"
                            class="worker-focus inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-Alumco-blue ring-1 ring-Alumco-blue/10"
                            data-pdf-zoom-in
                            aria-label="Aumentar zoom">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/>
                        </svg>
                    </button>
                    @if ($finalDownloadUrl)
                        <span class="mx-1 h-8 w-px bg-Alumco-blue/10"></span>
                        <a href="{{ $finalDownloadUrl }}"
                           download
                           class="worker-focus inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-Alumco-blue ring-1 ring-Alumco-blue/10 hover:bg-Alumco-blue/5"
                           title="Descargar PDF original"
                           aria-label="Descargar PDF original">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>

            <div class="max-h-[78vh] overflow-auto px-3 py-5" data-pdf-stage>
                <canvas class="mx-auto rounded-lg bg-white shadow-sm ring-1 ring-Alumco-blue/10" data-pdf-canvas></canvas>
            </div>
        </div>

    @elseif ($isPowerPoint)
        <div class="worker-card bg-white p-8 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-Alumco-blue/10 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-Alumco-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                </svg>
            </div>
            <h3 class="font-bold text-Alumco-gray text-xl mb-1">{{ $nombreOriginal ?? 'Presentación' }}</h3>
            <p class="text-base text-Alumco-gray/65 mb-6">Este archivo es una presentación de PowerPoint (.{{ strtoupper($extension) }})</p>
            
            <a href="{{ $finalDownloadUrl }}"
               class="worker-focus inline-flex items-center gap-2 bg-Alumco-blue text-white font-bold py-3.5 px-8 rounded-xl hover:bg-Alumco-blue/90 transition-all shadow-sm">
                Descargar presentación
            </a>
        </div>

    @else
        <div class="worker-card bg-gray-50/50 p-8 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-Alumco-blue/10 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-Alumco-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </div>
            <h3 class="font-bold text-Alumco-gray text-xl mb-1">Archivo adjunto</h3>
            <p class="text-base text-Alumco-gray/65 mb-6">Este archivo está en formato .{{ strtoupper($extension) }}</p>
            
            <a href="{{ $finalDownloadUrl }}"
               class="worker-focus inline-flex items-center gap-2 bg-Alumco-blue text-white font-bold py-3.5 px-8 rounded-xl hover:bg-Alumco-blue/90 transition-all shadow-sm">
                Abrir archivo
            </a>
        </div>
    @endif
@else
    <div class="worker-card p-4 bg-red-50 text-red-700 text-center text-base font-bold">
        No se encontró ningún archivo adjunto.
    </div>
@endif
