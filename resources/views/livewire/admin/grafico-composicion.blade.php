<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Por Estamento --}}
    <div style="height: 300px;">
        @if(empty($porEstamento['labels']))
            <p class="text-center text-gray-500 py-8 text-sm">Sin datos</p>
        @else
            <canvas id="graficoEstamentoChart"></canvas>
        @endif
    </div>

    {{-- Por Aprobación --}}
    <div style="height: 300px;">
        @if(empty($porAprobacion['labels']))
            <p class="text-center text-gray-500 py-8 text-sm">Sin datos</p>
        @else
            <canvas id="graficoAprobacionChart"></canvas>
        @endif
    </div>

    {{-- Por Sexo --}}
    <div style="height: 300px;">
        @if(empty($porSexo['labels']))
            <p class="text-center text-gray-500 py-8 text-sm">Sin datos</p>
        @else
            <canvas id="graficoSexoChart"></canvas>
        @endif
    </div>
</div>

@script
<script>
    (function() {
        const colors = ['#205099', '#4CAF50', '#A5B6F5', '#F8B606', '#FF6364'];

        // Gráfico por Estamento
        (function() {
            const data = @json($porEstamento);
            if (data.labels && data.labels.length > 0) {
                const ctx = document.getElementById('graficoEstamentoChart')?.getContext('2d');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: colors,
                                borderColor: 'white',
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 10,
                                        font: { size: 11 },
                                    }
                                }
                            },
                            animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : undefined,
                        }
                    });
                }
            }
        })();

        // Gráfico por Aprobación
        (function() {
            const data = @json($porAprobacion);
            if (data.labels && data.labels.length > 0) {
                const ctx = document.getElementById('graficoAprobacionChart')?.getContext('2d');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: ['#4CAF50', '#E0E0E0'],
                                borderColor: 'white',
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 10,
                                        font: { size: 11 },
                                    }
                                }
                            },
                            animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : undefined,
                        }
                    });
                }
            }
        })();

        // Gráfico por Sexo
        (function() {
            const data = @json($porSexo);
            if (data.labels && data.labels.length > 0) {
                const ctx = document.getElementById('graficoSexoChart')?.getContext('2d');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: colors,
                                borderColor: 'white',
                                borderWidth: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        padding: 10,
                                        font: { size: 11 },
                                    }
                                }
                            },
                            animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : undefined,
                        }
                    });
                }
            }
        })();
    })();
</script>
@endscript
