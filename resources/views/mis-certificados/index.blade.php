@extends('layouts.user')
@section('title', 'Mis certificados — Alumco')

@section('content')

<div x-data="{
        search: '',
        titles: {{ Js::from($certificados->map(fn($c) => strtolower($c->curso->titulo))->values()) }},
        get hasResults() {
            const q = this.search.toLowerCase().trim();
            return !q || this.titles.some(t => t.includes(q));
        }
     }"
     class="space-y-6">

    {{-- Cabecera + buscador --}}
    <section class="worker-card p-5 lg:p-7">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-display text-3xl font-black text-Alumco-gray">Mis certificados</h1>
                <p class="mt-1.5 text-base text-Alumco-gray/70">
                    @if ($certificados->isNotEmpty())
                        {{ $certificados->count() }} {{ $certificados->count() === 1 ? 'certificado obtenido' : 'certificados obtenidos' }}
                    @else
                        Completa un curso para obtener tu primer certificado.
                    @endif
                </p>
            </div>

            @if ($certificados->isNotEmpty())
            <div class="relative sm:w-72 shrink-0">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-Alumco-gray/40"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.636 5.636a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
                <input x-model="search"
                       type="search"
                       placeholder="Buscar por curso…"
                       class="w-full rounded-2xl border border-Alumco-gray/15 bg-white py-2.5 pl-10 pr-4 text-sm font-semibold text-Alumco-gray placeholder:text-Alumco-gray/40 focus:border-Alumco-blue/30 focus:outline-none focus:ring-4 focus:ring-Alumco-blue/10 transition-shadow">
            </div>
            @endif
        </div>
    </section>

    @if ($certificados->isNotEmpty())

        {{-- Grid de certificados --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($certificados as $cert)
            @php $accent = $cert->curso->color_promedio ?? '#205099'; @endphp
            <article
                data-title="{{ strtolower($cert->curso->titulo) }}"
                x-show="!search.trim() || $el.dataset.title.includes(search.toLowerCase().trim())"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="worker-card group flex flex-col overflow-hidden">

                {{-- Imagen del curso --}}
                <div class="relative aspect-video overflow-hidden"
                     style="--course-accent: {{ $accent }}; background: linear-gradient(135deg, {{ $accent }}, color-mix(in srgb, {{ $accent }} 55%, white))">
                    @if ($cert->curso->imagen_portada)
                        <img src="{{ asset('storage/' . $cert->curso->imagen_portada) }}"
                             alt="{{ $cert->curso->titulo }}"
                             class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
                    @endif

                    {{-- Badge --}}
                    <div class="absolute right-3 top-3 flex items-center gap-1.5 rounded-full bg-white/95 px-2.5 py-1 shadow-sm backdrop-blur-sm">
                        <svg class="h-3 w-3 text-Alumco-yellow" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 1 9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1Z"/>
                        </svg>
                        <span class="text-[10px] font-black uppercase tracking-wide text-Alumco-gray">Certificado</span>
                    </div>
                </div>

                {{-- Contenido --}}
                <div class="flex flex-1 flex-col gap-4 p-4">
                    <div class="flex-1">
                        <p class="line-clamp-2 text-base font-black leading-snug text-Alumco-gray">
                            {{ $cert->curso->titulo }}
                        </p>
                        <p class="mt-1.5 text-sm font-semibold text-Alumco-gray/55">
                            {{ $cert->fecha_emision?->isoFormat('D [de] MMMM [de] YYYY') ?? '—' }}
                        </p>
                    </div>

                    <a href="{{ route('mis-certificados.descargar', $cert) }}"
                       class="worker-focus inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-Alumco-blue px-4 py-3 text-sm font-black text-white shadow-sm transition-all hover:brightness-110 active:scale-[0.98]">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        Descargar PDF
                    </a>
                </div>
            </article>
            @endforeach
        </div>

        {{-- Sin resultados de búsqueda --}}
        <div x-show="search.trim() && !hasResults" x-cloak class="worker-soft-panel px-5 py-14 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-Alumco-gray/10">
                <svg class="h-7 w-7 text-Alumco-gray/35" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.636 5.636a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>
            <p class="text-base font-bold text-Alumco-gray/50">
                Ningún certificado coincide con "<span x-text="search" class="text-Alumco-gray"></span>"
            </p>
        </div>

    @else
        {{-- Sin certificados en absoluto --}}
        <section class="worker-card px-5 py-16 text-center">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full bg-Alumco-blue/10">
                <svg class="h-10 w-10 text-Alumco-blue/45" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 1 9.17 6.36 3 7.27l4.5 4.38L6.34 18 12 15l5.66 3-.84-6.35L21 7.27l-6.17-.91L12 1Z"/>
                </svg>
            </div>
            <h2 class="font-display text-2xl font-black text-Alumco-gray">Aún no tienes certificados</h2>
            <p class="mx-auto mt-3 max-w-md text-base leading-relaxed text-Alumco-gray/70">
                Completa un curso al 100% para obtener tu primer certificado.
            </p>
            <a href="{{ route('cursos.index') }}"
               class="btn-primary worker-focus mt-6 inline-flex rounded-full bg-Alumco-blue px-8 py-4 text-lg font-black text-white shadow-sm">
                Ir a mis cursos
            </a>
        </section>
    @endif

</div>

@endsection
