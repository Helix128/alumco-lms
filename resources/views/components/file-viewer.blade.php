@props(['rutaArchivo', 'archivoUrl' => null, 'descargarUrl' => null, 'nombreOriginal' => null])

@php
    $extension = $rutaArchivo ? strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION)) : null;
    $url = $archivoUrl ?? ($rutaArchivo ? Storage::url($rutaArchivo) : null);
    $finalDownloadUrl = $descargarUrl ?? $url;
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

    @elseif ($extension === 'pdf')
        <div class="worker-card overflow-hidden">
            <iframe src="{{ $url }}" 
                    class="w-full h-screen lg:h-[680px]"
                    type="application/pdf">
                <p class="p-4 text-center text-base text-Alumco-gray">
                    Tu navegador no puede mostrar el PDF. 
                    <a href="{{ $finalDownloadUrl }}" 
                       download="{{ $nombreOriginal }}" 
                       class="text-Alumco-blue font-semibold underline ml-1">Descargar PDF</a>
                </p>
            </iframe>
        </div>

    @else
        <div class="worker-card bg-gray-50 p-6 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-Alumco-blue/10 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-Alumco-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </div>
            <h3 class="font-bold text-Alumco-gray text-xl mb-1">Archivo adjunto</h3>
            <p class="text-base text-Alumco-gray/65 mb-4">Este archivo está en formato .{{ strtoupper($extension) }}</p>
            
            <a href="{{ $finalDownloadUrl }}" download="{{ $nombreOriginal }}"
               class="worker-focus inline-flex items-center gap-2 bg-Alumco-blue text-white font-bold py-3 px-6 rounded-full hover:bg-Alumco-blue/90 transition-colors shadow-sm">
                Descargar archivo
            </a>
        </div>
    @endif
@else
    <div class="worker-card p-4 bg-red-50 text-red-700 text-center text-base font-bold">
        No se encontró ningún archivo adjunto.
    </div>
@endif
