<div style="height: 300px;">
    @if(empty($chartData['labels']))
        <p class="text-center text-gray-500 py-8">No hay datos disponibles</p>
    @else
        <canvas id="graficoPorSedeChart"></canvas>
    @endif
</div>

@script
<script>
    (function() {
        const chartData = @json($chartData);
        if (!chartData.labels || chartData.labels.length === 0) return;

        const ctx = document.getElementById('graficoPorSedeChart')?.getContext('2d');
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Porcentaje de Aprobación',
                    data: chartData.aprobados,
                    backgroundColor: '#205099',
                    borderRadius: 6,
                    borderSkipped: false,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                        }
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
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : undefined,
            }
        });
    })();
</script>
@endscript
