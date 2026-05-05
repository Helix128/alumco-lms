<div style="height: 260px; position: relative;">
    <canvas id="gaugeAnualChart"></canvas>
</div>

@script
<script>
    (function() {
        const ctx = document.getElementById('gaugeAnualChart')?.getContext('2d');
        if (!ctx) return;

        const percentage = {{ $porcentaje }};
        const remaining = 100 - percentage;

        new Chart(ctx, {
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
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            font: { size: 12 },
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
                animation: window.AlumcoAccessibility?.isReducedMotion?.() ? false : undefined,
            }
        });

        // Center text
        const canvas = document.getElementById('gaugeAnualChart');
        const parent = canvas.parentElement;
        const textDiv = document.createElement('div');
        textDiv.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        `;
        textDiv.innerHTML = `
            <div style="font-size: 32px; font-weight: bold; color: #4CAF50;">
                {{ $porcentaje }}%
            </div>
            <div style="font-size: 12px; color: #666; margin-top: 4px;">
                Capacitados
            </div>
        `;
        parent.appendChild(textDiv);
    })();
</script>
@endscript
