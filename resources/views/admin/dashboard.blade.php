@extends('layouts.panel')

@section('title', 'Dashboard')

@section('header_title', 'Dashboard Analítico')

@section('content')
    @php
        $lmsStats = array_merge([
            'total_participantes' => 0,
            'iniciados' => 0,
            'completados' => 0,
            'en_riesgo' => 0,
            'feedback_promedio' => null,
        ], $lmsStats ?? []);
    @endphp

    <div class="space-y-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-white/80 bg-gradient-to-br from-[#205099] via-[#214f98] to-[#16356a] px-7 py-8 text-white shadow-[0_28px_72px_rgba(32,80,153,0.24)] lg:px-10 lg:py-10">
            <div class="absolute -right-10 top-0 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-28 w-28 rounded-full bg-[#A5B6F5]/20 blur-2xl"></div>

            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-[11px] font-black uppercase tracking-[0.28em] text-white/55">Centro de gestión</p>
                    <h2 class="mt-3 font-display text-3xl font-black tracking-tight text-white sm:text-4xl">
                        Dashboard analítico de la operación
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/72 sm:text-base">
                        Vista ejecutiva de usuarios activos, cursos, certificados y distribución del alumnado para detectar oportunidades sin navegar entre reportes.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 shadow-[0_16px_30px_rgba(15,23,42,0.12)] backdrop-blur">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/55">Actualizado</p>
                        <p class="mt-1 text-sm font-bold text-white">{{ now()->format('d/m/Y H:i') }}</p>
                    </div>
                    <a href="{{ route('admin.reportes.index') }}" wire:navigate.hover class="admin-action-button bg-white text-Alumco-blue shadow-none hover:bg-white/95">
                        Ver reportes
                    </a>
                </div>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-surface relative overflow-hidden p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-Alumco-blue to-Alumco-cyan"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Usuarios activos</p>
                        <p class="mt-2 font-display text-3xl font-black tracking-tight text-Alumco-blue">{{ $stats['totalUsers'] }}</p>
                        <p class="mt-2 text-sm font-medium text-Alumco-gray/65">Base activa disponible para capacitación</p>
                    </div>
                    <div class="rounded-2xl bg-Alumco-blue/8 p-3 text-Alumco-blue">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                        </svg>
                    </div>
                </div>
            </article>

            <article class="admin-surface relative overflow-hidden p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-Alumco-cyan to-Alumco-blue"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Cursos activos</p>
                        <p class="mt-2 font-display text-3xl font-black tracking-tight text-Alumco-cyan">{{ $stats['totalCursos'] }}</p>
                        <p class="mt-2 text-sm font-medium text-Alumco-gray/65">Cursos vigentes registrados en el sistema</p>
                    </div>
                    <div class="rounded-2xl bg-Alumco-cyan/20 p-3 text-Alumco-blue">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.669 0-3.218.51-4.5 1.385A7.968 7.968 0 009 4.804z" />
                        </svg>
                    </div>
                </div>
            </article>

            <article class="admin-surface relative overflow-hidden p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-Alumco-green-vivid to-Alumco-green"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-green-accessible/75">Certificados</p>
                        <p class="mt-2 font-display text-3xl font-black tracking-tight text-Alumco-green-vivid">{{ $stats['totalCertificados'] }}</p>
                        <p class="mt-2 text-sm font-medium text-Alumco-gray/65">Emitidos durante {{ now()->year }}</p>
                    </div>
                    <div class="rounded-2xl bg-Alumco-green/25 p-3 text-Alumco-green-accessible">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c.607-1.03 1.754-1.722 3-1.722 1.933 0 3.5 1.567 3.5 3.5 0 2.55-2.25 4.222-6.5 7.222-4.25-3-6.5-4.672-6.5-7.222 0-1.933 1.567-3.5 3.5-3.5 1.246 0 2.393.692 3 1.722z" />
                        </svg>
                    </div>
                </div>
            </article>

            <article class="admin-surface relative overflow-hidden p-6">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-Alumco-coral to-Alumco-yellow"></div>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-coral-accessible/80">Cumplimiento</p>
                        <p class="mt-2 font-display text-3xl font-black tracking-tight text-Alumco-coral">{{ $stats['cumplimientoAnual'] }}%</p>
                        <p class="mt-2 text-sm font-medium text-Alumco-gray/65">Usuarios con certificado emitido en el año</p>
                    </div>
                    <div class="rounded-2xl bg-Alumco-coral/10 p-3 text-Alumco-coral-accessible">
                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m6-2A10 10 0 1112 2a10 10 0 010 20z" />
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        <section class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-5">
            <article class="admin-surface p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Participantes asignados</p>
                <p class="mt-2 font-display text-2xl font-black text-Alumco-blue">{{ $lmsStats['total_participantes'] }}</p>
            </article>
            <article class="admin-surface p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Iniciaron</p>
                <p class="mt-2 font-display text-2xl font-black text-Alumco-cyan">{{ $lmsStats['iniciados'] }}</p>
            </article>
            <article class="admin-surface p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Completaron</p>
                <p class="mt-2 font-display text-2xl font-black text-Alumco-green-accessible">{{ $lmsStats['completados'] }}</p>
            </article>
            <article class="admin-surface p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">En riesgo</p>
                <p class="mt-2 font-display text-2xl font-black text-Alumco-coral-accessible">{{ $lmsStats['en_riesgo'] }}</p>
            </article>
            <article class="admin-surface p-5">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Feedback promedio</p>
                <p class="mt-2 font-display text-2xl font-black text-Alumco-yellow">{{ $lmsStats['feedback_promedio'] ?: '—' }}</p>
            </article>
        </section>

        <section class="grid gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-7">
                @livewire('admin.certificados-por-mes')
                @livewire('admin.cursos-por-sede')
            </div>

            <div class="space-y-6 lg:col-span-5">
                @livewire('admin.gauge-anual-capacitador', ['porcentaje' => $stats['cumplimientoAnual']])
                @livewire('admin.grafico-por-sede')
            </div>
        </section>

        <section class="grid gap-6">
            @livewire('admin.grafico-composicion')
            @livewire('admin.distribucion-etaria')
        </section>
    </div>
@endsection
