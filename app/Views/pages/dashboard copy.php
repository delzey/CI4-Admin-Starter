<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0"><?= esc($title) ?></h3>
    <span class="text-muted small">
        Welcome, <?= esc($authUser->username ?? $authUser->email ?? 'User') ?>
    </span>
</div>

<div id="dashboard">
    <div class="row g-3">
        <!-- Summary Cards -->
        <div class="col-md-3">
            <div class="card text-bg-primary h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-0">Quotes Today</h5>
                    <h2 id="quotes_today" class="mt-1">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-0">Invoices Today</h5>
                    <h2 id="invoices_today" class="mt-1">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-info h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-0">New Users</h5>
                    <h2 id="new_users" class="mt-1">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning h-100">
                <div class="card-body text-center">
                    <h5 class="card-title mb-0">Open Tasks</h5>
                    <h2 id="open_tasks" class="mt-1">0</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Weekly Activity</h5>
            <ul class="nav nav-pills card-header-pills" id="chartTabs">
                <li class="nav-item"><a class="nav-link active" data-period="week" href="#">Week</a></li>
                <li class="nav-item"><a class="nav-link" data-period="month" href="#">Month</a></li>
                <li class="nav-item"><a class="nav-link" data-period="year" href="#">Year</a></li>
            </ul>
        </div>
        <div class="card-body">
            <canvas id="chartCanvas" height="120"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartCanvas').getContext('2d');
    let chart;

    function loadDashboardData() {
        fetch('<?= site_url('dashboard/widgets') ?>')
            .then(res => res.json())
            .then(data => {
                const summary = data?.summary ?? {};
                const chartData = data?.chart ?? { labels: [], values: [] };

                // Guard: fill missing values with 0s
                document.getElementById('quotes_today').textContent   = summary.quotes_today   ?? 0;
                document.getElementById('invoices_today').textContent = summary.invoices_today ?? 0;
                document.getElementById('new_users').textContent      = summary.new_users      ?? 0;
                document.getElementById('open_tasks').textContent     = summary.open_tasks     ?? 0;

                const labels = Array.isArray(chartData.labels) ? chartData.labels : [];
                const values = Array.isArray(chartData.values) ? chartData.values : [];

                if (chart) chart.destroy();
                chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Activity',
                            data: values,
                            borderWidth: 1,
                            backgroundColor: 'rgba(54, 162, 235, 0.5)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true } }
                    }
                });
            })
            .catch(err => console.error('Dashboard widget load failed:', err));
            }

    loadDashboardData();
});
</script>

<?= $this->endSection() ?>
