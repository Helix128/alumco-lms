<x-admin.chart-panel
    title="Cursos programados por sede"
    description="Compara cursos únicos y sesiones planificadas del año en curso."
    :badge="$sedeLider"
    accent="165 182 245"
    canvas-class="chart-panel__canvas chart-panel__canvas--lg"
>
    @if(empty($chartData['labels']))
        <div class="chart-panel__placeholder">Todavía no hay planificaciones asignadas a sedes este año.</div>
    @else
        <div class="mb-4 flex flex-wrap items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-Alumco-blue/60">
            <span class="rounded-full bg-Alumco-blue/8 px-3 py-1 text-Alumco-blue">Cursos únicos: {{ $totalCursos }}</span>
            <span class="rounded-full bg-Alumco-cyan/20 px-3 py-1 text-Alumco-gray">Sesiones: {{ $totalPlanificaciones }}</span>
            <span class="rounded-full bg-Alumco-green/20 px-3 py-1 text-Alumco-green-accessible">Lidera: {{ $sedeLiderCantidad }}</span>
        </div>
        <div wire:ignore class="relative h-full">
            <canvas id="cursosPorSedeChart"></canvas>
        </div>
    @endif
</x-admin.chart-panel>

@script
<script>
    (() => {
        const data = @json($chartData);
        if (! data.labels || data.labels.length === 0) {
            return;
        }

        window.AlumcoCharts?.render('cursosPorSedeChart', {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Cursos únicos',
                        data: data.uniqueCourses,
                        backgroundColor: '#205099',
                        borderRadius: 10,
                        borderSkipped: false,
                    },
                    {
                        label: 'Planificaciones',
                        data: data.plannedSessions,
                        backgroundColor: 'rgba(165, 182, 245, 0.9)',
                        borderRadius: 10,
                        borderSkipped: false,
                    },
                ],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'rectRounded',
                            padding: 18,
                            boxWidth: 12,
                            color: '#4A4A4A',
                            font: {
                                weight: '700',
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            color: '#6b7280',
                            font: {
                                weight: '700',
                            },
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.16)',
                        },
                    },
                    y: {
                        ticks: {
                            color: '#4A4A4A',
                            font: {
                                weight: '800',
                            },
                        },
                        grid: {
                            display: false,
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
</script>
@endscript
