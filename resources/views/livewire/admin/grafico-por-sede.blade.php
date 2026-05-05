<x-admin.chart-panel
    title="Aprobación por sede"
    description="Porcentaje de usuarios certificados sobre el total activo en cada sede."
    :badge="count($chartData['labels'] ?? []) . ' sedes'"
    accent="205 116 44"
    canvas-class="chart-panel__canvas chart-panel__canvas--lg"
>
    @if(empty($chartData['labels']))
        <div class="chart-panel__placeholder">No hay datos disponibles para sedes.</div>
    @else
        <div wire:ignore class="relative h-full">
            <canvas id="graficoPorSedeChart"></canvas>
        </div>
    @endif
</x-admin.chart-panel>

@script
<script>
    (function() {
        const chartData = @json($chartData);
        if (!chartData.labels || chartData.labels.length === 0) return;

        window.AlumcoCharts?.render('graficoPorSedeChart', {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Porcentaje de Aprobación',
                    data: chartData.aprobados,
                    backgroundColor: '#205099',
                    borderRadius: 10,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'nearest',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback(value) {
                                return value + '%';
                            },
                            color: '#6b7280',
                            font: {
                                weight: '700',
                            },
                        },
                        grid: {
                            color: 'rgba(148, 163, 184, 0.16)',
                        },
                    }
                },
                animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : {
                    duration: 900,
                    easing: 'easeOutQuart',
                },
            }
        });
    })();
</script>
@endscript
