@extends('layouts.panel')

@section('title', 'Salud LMS')
@section('header_title', 'Salud LMS')

@section('content')
    <div class="space-y-8">
        <div>
            <h2 class="admin-page-title">Salud operacional del LMS</h2>
            <p class="admin-page-subtitle">Señales técnicas y de capacitación que requieren revisión del equipo dev.</p>
        </div>

        <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
            <article class="admin-surface p-6">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Jobs fallidos</p>
                <p class="mt-2 font-display text-3xl font-black text-Alumco-coral-accessible">{{ $health['jobs_fallidos'] }}</p>
            </article>
            <article class="admin-surface p-6">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Notificaciones 7 días</p>
                <p class="mt-2 font-display text-3xl font-black text-Alumco-blue">{{ $health['notificaciones_7d'] }}</p>
            </article>
            <article class="admin-surface p-6">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Feedback plataforma</p>
                <p class="mt-2 font-display text-3xl font-black text-Alumco-green-accessible">{{ $health['feedback_plataforma_nuevo'] }}</p>
            </article>
            <article class="admin-surface p-6">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Tickets abiertos</p>
                <p class="mt-2 font-display text-3xl font-black text-Alumco-blue">{{ $health['tickets_abiertos'] }}</p>
            </article>
            <article class="admin-surface p-6">
                <p class="text-[10px] font-black uppercase tracking-[0.24em] text-Alumco-blue/55">Tickets críticos</p>
                <p class="mt-2 font-display text-3xl font-black text-Alumco-coral-accessible">{{ $health['tickets_criticos'] }}</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="admin-surface p-6">
                <h3 class="text-sm font-black text-Alumco-blue uppercase tracking-[0.2em]">Alertas de configuración</h3>
                <div class="mt-5 space-y-3">
                    @foreach ($health['alertas'] as $alerta)
                        <div class="flex items-center justify-between gap-4 rounded-2xl border border-gray-100 bg-white px-4 py-3">
                            <span class="text-sm font-bold text-Alumco-gray">{{ $alerta['label'] }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-black {{ $alerta['level'] === 'danger' ? 'bg-Alumco-coral/10 text-Alumco-coral-accessible' : 'bg-Alumco-yellow/15 text-Alumco-gray' }}">
                                {{ $alerta['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="admin-surface p-6">
                <h3 class="text-sm font-black text-Alumco-blue uppercase tracking-[0.2em]">Tareas recientes</h3>
                <div class="mt-5 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="pb-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Comando</th>
                                <th class="pb-3 text-[10px] font-black uppercase tracking-widest text-gray-400">Estado</th>
                                <th class="pb-3 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Procesados</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($health['tareas_recientes'] as $run)
                                <tr>
                                    <td class="py-3 pr-4 text-sm font-bold text-Alumco-gray">{{ $run->command }}</td>
                                    <td class="py-3 pr-4 text-xs font-black uppercase text-Alumco-blue">{{ $run->status }}</td>
                                    <td class="py-3 text-right text-sm font-black text-Alumco-gray">{{ $run->processed_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-sm font-bold text-Alumco-gray/40">Sin ejecuciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
