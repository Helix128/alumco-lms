@props(['rutaArchivo', 'archivoUrl' => null, 'descargarUrl' => null, 'nombreOriginal' => null])

@php
    $extension = $rutaArchivo ? strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION)) : null;
    $url = $archivoUrl ?? ($rutaArchivo ? Storage::url($rutaArchivo) : null);
    $finalDownloadUrl = $descargarUrl ?? $url;

    $isPdf = $extension === 'pdf';
    $isPowerPoint = in_array($extension, ['ppt', 'pptx']);
@endphp

@if ($extension)
    @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
        <img src="{{ $url }}" 
             alt="Archivo de módulo" 
             class="worker-card w-full object-contain max-h-96 lg:max-h-[640px]">

    @elseif (in_array($extension, ['mp4', 'webm', 'ogg']))
        <video src="{{ $url }}" 
               controls 
               class="worker-card w-full max-h-72 lg:max-h-[560px]"
               preload="metadata">
            Tu navegador no soporta la reproducción de video.
        </video>

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
