<footer class="dashboard-footer">
    <div>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
    <div>
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Contact</a>
        <a href="#"><i class="bi bi-github"></i></a>
        <a href="#"><i class="bi bi-twitter"></i></a>
    </div>
</footer>

<!-- Bootstrap JS -->
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> --}}

<!-- AOS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Custom JS -->
<script>
    AOS.init({
        duration: 800,
        once: true,
        offset: 50,
    });

    // Sample charts
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const ctx1 = document.getElementById('revenueChart')?.getContext('2d');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Revenue 2024',
                        data: [12000, 19000, 15000, 22000, 28000, 35000, 42000, 48000, 53000, 58000, 62000, 68000],
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#4361ee',
                        pointBorderColor: 'white',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#e2e8f0' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // Traffic Sources Pie Chart
        const ctx2 = document.getElementById('trafficChart')?.getContext('2d');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Direct', 'Social', 'Email', 'Organic'],
                    datasets: [{
                        data: [45, 25, 15, 15],
                        backgroundColor: ['#4361ee', '#f72585', '#4cc9f0', '#f8961e'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' },
                    },
                    cutout: '70%',
                }
            });
        }
    });
</script>
