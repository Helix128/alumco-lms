<div>
    @if (count($chartData) === 0)
        <p class="text-Alumco-gray/50 text-sm text-center py-6">
            No hay datos de progreso disponibles aún.
        </p>
    @else
        <div class="relative" style="height: 260px;">
            <canvas id="estadisticasChart"></canvas>
        </div>
        @push('scripts')
        <script>
            (function() {
                const data = @json($chartData);
                const labels = data.map(d => d.label.length > 20 ? d.label.substring(0, 20) + '…' : d.label);
                const values = data.map(d => d.value);
                const isReducedMotion = window.AlumcoAccessibility?.isReducedMotion();

                new Chart(document.getElementById('estadisticasChart'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: '% Completado',
                            data: values,
                            backgroundColor: '#205099',
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        animation: isReducedMotion ? false : { duration: 1000 },
                        responsive: true,
                        maintainAspectRatio: false,                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } }
                        },
                        plugins: { legend: { display: false } }
                    }
                });
            })();
        </script>
        @endpush
    @endif
</div>
