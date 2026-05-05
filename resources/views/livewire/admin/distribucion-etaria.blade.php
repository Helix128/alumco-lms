<x-admin.chart-panel
    title="Distribución etaria"
    description="Agrupa a los usuarios activos por rangos de edad."
    :badge="$rangoDominante"
    accent="248 182 6"
    canvas-class="chart-panel__canvas chart-panel__canvas--sm"
>
    @if(empty($chartData['labels']) || empty(array_filter($chartData['data'] ?? [])))
        <div class="chart-panel__placeholder">No hay usuarios suficientes para calcular la distribución etaria.</div>
    @else
        <div class="mb-4 flex flex-wrap items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-Alumco-blue/60">
            <span class="rounded-full bg-Alumco-yellow/20 px-3 py-1 text-Alumco-gold-accessible">Usuarios: {{ $totalUsuarios }}</span>
            <span class="rounded-full bg-Alumco-coral/10 px-3 py-1 text-Alumco-coral-accessible">Rango líder: {{ $rangoDominanteCantidad }}</span>
        </div>
        <div wire:ignore class="relative h-full">
            <canvas id="distribucionEtariaChart"></canvas>
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

        window.AlumcoCharts?.render('distribucionEtariaChart', {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: [
                        '#205099',
                        '#A5B6F5',
                        '#4CAF50',
                        '#F8B606',
                        '#FF6364',
                    ],
                    borderColor: '#ffffff',
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
                            padding: 18,
                            boxWidth: 10,
                            color: '#4A4A4A',
                            font: {
                                weight: '700',
                            },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `${context.label}: ${context.parsed} usuarios`;
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
</script>
@endscript
