@extends('layouts.help')

@section('title', 'Centro de ayuda')

@section('content')
    <nav class="mb-5 text-sm font-bold text-gray-500" aria-label="Migas de pan">
        <a class="text-Alumco-blue underline underline-offset-4" href="{{ auth()->check() ? route(\App\Support\UserAreaRedirector::canonicalRouteName(auth()->user())) : route('login') }}">Inicio</a>
        <span aria-hidden="true"> / </span><span aria-current="page">Ayuda</span>
    </nav>

    <section class="rounded-3xl bg-Alumco-blue px-6 py-10 text-white shadow-xl lg:px-12">
        <p class="text-xs font-black uppercase tracking-[.2em] text-white">Ayuda y documentación</p>
        <h1 class="mt-2 font-display text-3xl font-black lg:text-5xl">¿Qué necesitas resolver?</h1>
        <p class="mt-3 max-w-2xl text-base font-medium text-white/85">Busca instrucciones breves y autorizadas para tu rol. Cada tema incluye pasos concretos y acceso al soporte.</p>
        <form method="GET" action="{{ route('help.index') }}" class="mt-7 flex flex-col gap-3 sm:flex-row" role="search">
            <label for="help-search" class="sr-only">Buscar en el centro de ayuda</label>
            <input id="help-search" name="buscar" type="search" value="{{ $search }}" placeholder="Ejemplo: descargar certificado" class="min-h-12 flex-1 rounded-2xl border-2 border-transparent bg-white px-5 font-bold text-Alumco-gray placeholder:text-gray-400 focus:border-Alumco-yellow focus:outline-none">
            <button class="min-h-12 rounded-2xl bg-Alumco-yellow px-6 font-black text-Alumco-gray">Buscar ayuda</button>
        </form>
    </section>

    <section class="mt-8" aria-labelledby="help-results-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 id="help-results-title" class="font-display text-2xl font-black text-Alumco-blue">{{ $search ? 'Resultados de búsqueda' : 'Temas disponibles' }}</h2>
                <p class="mt-1 text-sm font-medium text-gray-500" aria-live="polite">{{ $topics->count() }} {{ $topics->count() === 1 ? 'tema disponible' : 'temas disponibles' }}.</p>
            </div>
            @if($search)
                <a href="{{ route('help.index') }}" class="worker-focus inline-flex min-h-11 items-center rounded-xl px-4 font-bold text-Alumco-blue underline underline-offset-4">Limpiar búsqueda</a>
            @endif
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @forelse($topics as $slug => $topic)
                <a href="{{ route('help.show', $slug) }}" class="worker-focus group min-h-44 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-Alumco-blue/30 hover:shadow-lg motion-reduce:transition-none">
                    <h3 class="font-display text-lg font-black text-Alumco-blue group-hover:underline">{{ $topic['title'] }}</h3>
                    <p class="mt-2 text-sm font-medium leading-relaxed text-gray-600">{{ $topic['summary'] }}</p>
                    <span class="mt-5 inline-flex font-black text-Alumco-blue">Ver pasos <span aria-hidden="true">→</span></span>
                </a>
            @empty
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 md:col-span-2 lg:col-span-3" role="status">
                    <h3 class="font-display text-lg font-black text-amber-900">No encontramos un tema con esas palabras</h3>
                    <p class="mt-2 font-medium text-amber-800">Prueba con una tarea, como “evaluación”, “calendario” o “certificado”. También puedes contactar a soporte.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
