@php
    $isHighPerformance = $porcentaje >= 80;
@endphp

<x-admin.chart-panel
    title="Indicador anual de capacitación"
    description="Mide el avance de usuarios certificados respecto del total activo."
    :badge="$porcentaje . '%'"
    accent="76 175 80"
    canvas-class="chart-panel__canvas chart-panel__canvas--sm"
>
    <div wire:ignore class="relative h-full">
        <canvas id="gaugeAnualChart"></canvas>
        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <div class="font-display text-5xl font-black tracking-tight {{ $isHighPerformance ? 'text-Alumco-green-accessible' : 'text-Alumco-blue' }}">
                    {{ $porcentaje }}%
                </div>
                <div class="mt-1 text-[11px] font-black uppercase tracking-[0.2em] text-Alumco-gray/55">
                    Capacitación cumplida
                </div>
            </div>
        </div>
    </div>
</x-admin.chart-panel>

@script
<script>
    (function() {
        const percentage = {{ $porcentaje }};
        const remaining = 100 - percentage;

        window.AlumcoCharts?.render('gaugeAnualChart', {
            type: 'doughnut',
            data: {
                labels: ['Cumplido', 'Pendiente'],
                datasets: [{
                    data: [percentage, remaining],
                    backgroundColor: [
                        '#4CAF50',
                        '#E0E0E0'
                    ],
                    borderColor: 'white',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 18,
                            font: {
                                size: 12,
                                weight: '700',
                            },
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
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
