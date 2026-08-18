@extends('layouts.help')

@section('title', $topic['title'])

@section('content')
    <nav class="mb-5 text-sm font-bold text-gray-500" aria-label="Migas de pan">
        <a class="text-Alumco-blue underline underline-offset-4" href="{{ auth()->check() ? route(\App\Support\UserAreaRedirector::canonicalRouteName(auth()->user())) : route('login') }}">Inicio</a>
        <span aria-hidden="true"> / </span><a class="text-Alumco-blue underline underline-offset-4" href="{{ route('help.index') }}">Ayuda</a>
        <span aria-hidden="true"> / </span><span aria-current="page">{{ $topic['title'] }}</span>
    </nav>

    <article class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm lg:p-10">
            <p class="text-xs font-black uppercase tracking-[.2em] text-Alumco-blue">Guía paso a paso</p>
            <h1 class="mt-2 font-display text-3xl font-black text-Alumco-blue lg:text-4xl">{{ $topic['title'] }}</h1>
            <p class="mt-3 text-lg font-medium leading-relaxed text-gray-600">{{ $topic['summary'] }}</p>

            <h2 id="pasos" class="mt-9 font-display text-xl font-black text-Alumco-gray">Pasos</h2>
            <ol class="mt-4 space-y-4">
                @foreach($topic['steps'] as $index => $step)
                    <li class="flex gap-4 rounded-2xl bg-Alumco-cream p-4">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-Alumco-blue font-black text-white" aria-hidden="true">{{ $index + 1 }}</span>
                        <span class="pt-1 font-medium leading-relaxed text-gray-700">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <aside class="space-y-4" aria-label="Ayuda adicional">
            <div class="rounded-2xl border border-Alumco-blue/15 bg-Alumco-blue/5 p-5">
                <h2 class="font-display text-lg font-black text-Alumco-blue">Ten en cuenta</h2>
                <ul class="mt-3 space-y-3">
                    @foreach($topic['tips'] as $tip)
                        <li class="flex gap-2 text-sm font-medium leading-relaxed text-gray-700"><span class="font-black text-Alumco-blue" aria-hidden="true">✓</span>{{ $tip }}</li>
                    @endforeach
                </ul>
            </div>
            <a href="{{ auth()->check() ? route('support.index') : route('support.public.create') }}" class="worker-focus block rounded-2xl bg-Alumco-blue p-5 text-white shadow-lg">
                <strong class="font-display text-lg">¿Aún necesitas ayuda?</strong>
                <span class="mt-1 block text-sm font-medium text-white/85">Abre una solicitud de soporte en línea.</span>
            </a>
            <a href="{{ route('help.index') }}" class="worker-focus inline-flex min-h-11 items-center font-black text-Alumco-blue underline underline-offset-4">← Volver a todos los temas</a>
        </aside>
    </article>
@endsection
