@props(['rutaArchivo'])

@php
    $extension = $rutaArchivo ? strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION)) : null;
    $url = Storage::url($rutaArchivo); // o asset('storage/' . $rutaArchivo);
@endphp

@if ($extension)
    @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
        <img src="{{ $url }}" 
             alt="Archivo de módulo" 
             class="w-full rounded-2xl shadow-md object-cover max-h-96 lg:max-h-[600px]">

    @elseif (in_array($extension, ['mp4', 'webm', 'ogg']))
        <video src="{{ $url }}" 
               controls 
               class="w-full rounded-2xl shadow-md max-h-72 lg:max-h-[500px]" 
               preload="metadata">
            Tu navegador no soporta la reproducción de video.
        </video>

    @elseif ($extension === 'pdf')
        <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
            <iframe src="{{ $url }}" 
                    class="w-full h-screen lg:h-[600px]" 
                    type="application/pdf">
                <p class="p-4 text-center text-sm text-Alumco-gray">
                    Tu navegador no puede mostrar el PDF. 
                    <a href="{{ $url }}" 
                       target="_blank" 
                       class="text-Alumco-blue font-semibold underline ml-1">Descargar PDF</a>
                </p>
            </iframe>
        </div>

    @else
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-6 flex flex-col items-center justify-center text-center">
            <div class="w-16 h-16 bg-Alumco-blue/10 rounded-full flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-Alumco-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
            </div>
            <h3 class="font-bold text-Alumco-gray text-lg mb-1">Archivo adjunto</h3>
            <p class="text-sm text-Alumco-gray/60 mb-4">Este archivo está en formato .{{ strtoupper($extension) }}</p>
            
            <a href="{{ $url }}" download
               class="inline-flex items-center gap-2 bg-Alumco-blue text-white font-bold py-2.5 px-6 rounded-xl hover:bg-Alumco-blue/90 transition-colors shadow-sm">
                Descargar Archivo
            </a>
        </div>
    @endif
@else
    <div class="p-4 bg-red-50 text-red-600 rounded-xl text-center text-sm font-medium">
        No se encontró ningún archivo adjunto.
    </div>
@endif
