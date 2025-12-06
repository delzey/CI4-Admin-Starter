<?= $this->extend('layouts/main') ?>

<?= $this->section('pageTitle') ?>
<i class="fas fa-chart-line me-2"></i> <?= esc($title ?? 'Dashboard') ?>
<?= $this->endSection() ?>

<?= $this->section('pageSubtitle') ?>
Welcome, <?= esc($authUser->username ?? $authUser->email ?? 'User') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div id="dashboard">
    <div class="row g-3">

        <!-- KPI CARDS ------------------------------------------------------- -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-shape bg-primary text-white rounded me-3">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <h6 class="text-uppercase text-muted small mb-1">Quotes Today</h6>
                        <h4 class="fw-semibold mb-0" id="quotes_today">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-shape bg-success text-white rounded me-3">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h6 class="text-uppercase text-muted small mb-1">Invoices Today</h6>
                        <h4 class="fw-semibold mb-0" id="invoices_today">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-shape bg-info text-white rounded me-3">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h6 class="text-uppercase text-muted small mb-1">New Users</h6>
                        <h4 class="fw-semibold mb-0" id="new_users">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-shape bg-warning text-white rounded me-3">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <div>
                        <h6 class="text-uppercase text-muted small mb-1">Open Tasks</h6>
                        <h4 class="fw-semibold mb-0" id="open_tasks">0</h4>
                    </div>
                </div>
            </div>
        </div>


        <!-- CHART ------------------------------------------------------------ -->
        <div class="col-12">
            <div class="card border-0 shadow-sm mt-2">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold">Weekly Activity</h6>

                    <ul class="nav nav-pills small" id="chartTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-period="week" href="#">Week</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-period="month" href="#">Month</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-period="year" href="#">Year</a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <canvas id="chartCanvas" height="120"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>


<!-- JS -->
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('chartCanvas').getContext('2d');
    let chart;

    function loadDashboardData(period = 'week') {
        fetch('<?= site_url('dashboard/widgets') ?>?period=' + period)
            .then(res => res.json())
            .then(data => {
                const summary = data?.summary ?? {};
                const chartData = data?.chart ?? { labels: [], values: [] };

                // Summary cards
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
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            })
            .catch(err => console.error('Dashboard widget load failed:', err));
    }

    // initial load
    loadDashboardData();

    // tabs
    document.querySelectorAll('#chartTabs .nav-link').forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector('#chartTabs .active')?.classList.remove('active');
            this.classList.add('active');
            loadDashboardData(this.dataset.period);
        });
    });

});
</script>

<?= $this->endSection() ?>
