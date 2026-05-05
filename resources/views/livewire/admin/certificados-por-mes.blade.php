<x-admin.chart-panel
    title="Certificados emitidos por mes"
    description="La curva muestra el ritmo de emisión durante el año actual."
    :badge="$currentYear"
    accent="32 80 153"
    canvas-class="chart-panel__canvas chart-panel__canvas--lg"
>
    @if(empty($chartData['labels']) || empty(array_filter($chartData['data'] ?? [])))
        <div class="chart-panel__placeholder">Todavía no hay certificados emitidos este año.</div>
    @else
        <div wire:ignore class="relative h-full">
            <canvas id="certificadosPorMesChart"></canvas>
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

        window.AlumcoCharts?.render('certificadosPorMesChart', {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Certificados',
                    data: data.data,
                    borderColor: '#205099',
                    backgroundColor: 'rgba(32, 80, 153, 0.14)',
                    pointBackgroundColor: '#205099',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.38,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `${context.parsed.y} certificados`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#6b7280',
                            font: {
                                weight: '700',
                            },
                        },
                    },
                    y: {
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
