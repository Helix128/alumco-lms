@php
    $totalEstamentos = array_sum($porEstamento['data'] ?? []);
    $totalAprobacion = array_sum($porAprobacion['data'] ?? []);
    $totalSexo = array_sum($porSexo['data'] ?? []);
@endphp

<x-admin.chart-panel
    title="Composición institucional"
    description="Tres lecturas complementarias para entender la base activa."
    :badge="$totalEstamentos . ' usuarios'"
    accent="165 182 245"
    canvas-class="chart-panel__canvas chart-panel__canvas--lg"
>
    <div class="grid gap-4 xl:grid-cols-3">
        <div class="rounded-[1.5rem] border border-slate-100 bg-white/80 p-4 shadow-[0_16px_32px_rgba(32,80,153,0.05)]">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-Alumco-blue/60">Estamento</p>
                    <p class="mt-1 text-sm font-black text-Alumco-gray">Usuarios por área</p>
                </div>
                <span class="rounded-full bg-Alumco-blue/8 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-Alumco-blue">{{ $totalEstamentos }}</span>
            </div>

            @if(empty($porEstamento['labels']))
                <div class="chart-panel__placeholder chart-panel__placeholder--sm">Sin datos</div>
            @else
                <div wire:ignore class="relative h-[220px]">
                    <canvas id="graficoEstamentoChart"></canvas>
                </div>
            @endif
        </div>

        <div class="rounded-[1.5rem] border border-slate-100 bg-white/80 p-4 shadow-[0_16px_32px_rgba(32,80,153,0.05)]">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-Alumco-blue/60">Aprobación</p>
                    <p class="mt-1 text-sm font-black text-Alumco-gray">Con y sin certificado</p>
                </div>
                <span class="rounded-full bg-Alumco-green/20 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-Alumco-green-accessible">{{ $totalAprobacion }}</span>
            </div>

            @if(empty($porAprobacion['labels']))
                <div class="chart-panel__placeholder chart-panel__placeholder--sm">Sin datos</div>
            @else
                <div wire:ignore class="relative h-[220px]">
                    <canvas id="graficoAprobacionChart"></canvas>
                </div>
            @endif
        </div>

        <div class="rounded-[1.5rem] border border-slate-100 bg-white/80 p-4 shadow-[0_16px_32px_rgba(32,80,153,0.05)]">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-Alumco-blue/60">Sexo</p>
                    <p class="mt-1 text-sm font-black text-Alumco-gray">Composición declarada</p>
                </div>
                <span class="rounded-full bg-Alumco-coral/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-Alumco-coral-accessible">{{ $totalSexo }}</span>
            </div>

            @if(empty($porSexo['labels']))
                <div class="chart-panel__placeholder chart-panel__placeholder--sm">Sin datos</div>
            @else
                <div wire:ignore class="relative h-[220px]">
                    <canvas id="graficoSexoChart"></canvas>
                </div>
            @endif
        </div>
    </div>
</x-admin.chart-panel>

@script
<script>
    (() => {
        const colors = ['#205099', '#4CAF50', '#A5B6F5', '#F8B606', '#FF6364'];

        (function() {
            const data = @json($porEstamento);
            if (! data.labels || data.labels.length === 0) {
                return;
            }

            window.AlumcoCharts?.render('graficoEstamentoChart', {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: colors,
                        borderColor: 'white',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 14,
                                color: '#4A4A4A',
                                font: {
                                    size: 11,
                                    weight: '700',
                                },
                            },
                        },
                    },
                    animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : {
                        duration: 900,
                        easing: 'easeOutQuart',
                    },
                },
            });
        })();

        (function() {
            const data = @json($porAprobacion);
            if (! data.labels || data.labels.length === 0) {
                return;
            }

            window.AlumcoCharts?.render('graficoAprobacionChart', {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: ['#4CAF50', '#E0E0E0'],
                        borderColor: 'white',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 14,
                                color: '#4A4A4A',
                                font: {
                                    size: 11,
                                    weight: '700',
                                },
                            },
                        },
                    },
                    animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : {
                        duration: 900,
                        easing: 'easeOutQuart',
                    },
                },
            });
        })();

        (function() {
            const data = @json($porSexo);
            if (! data.labels || data.labels.length === 0) {
                return;
            }

            window.AlumcoCharts?.render('graficoSexoChart', {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.data,
                        backgroundColor: colors,
                        borderColor: 'white',
                        borderWidth: 2,
                        hoverOffset: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '64%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 14,
                                color: '#4A4A4A',
                                font: {
                                    size: 11,
                                    weight: '700',
                                },
                            },
                        },
                    },
                    animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : {
                        duration: 900,
                        easing: 'easeOutQuart',
                    },
                },
            });
        })();
    })();
</script>
@endscript
