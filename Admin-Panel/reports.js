/* reports.js */
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('monthlyChart');
    if (ctx) {
        const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(212, 175, 55, 0.2)');
        gradient.addColorStop(1, 'rgba(212, 175, 55, 0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labelsData, // Provided via PHP
                datasets: [{
                    label: 'Revenue (RS)',
                    data: valuesData, // Provided via PHP
                    borderColor: '#d4af37',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#d4af37',
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1a1a1a',
                        titleFont: { family: 'Cinzel' },
                        bodyFont: { family: 'Raleway' },
                        padding: 12,
                        borderColor: '#d4af37',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#888' } },
                    x: { grid: { display: false }, ticks: { color: '#888' } }
                }
            }
        });
    }
});

// Helper for dynamic labels (called in PHP)
function initChartData(labels, values) {
    window.labelsData = labels;
    window.valuesData = values;
}