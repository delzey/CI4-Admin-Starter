<!-- app/Views/layouts/main.php -->
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= esc($title ?? 'Dashboard') ?> | <?= esc(setting('App.appName') ?? 'CI4 Starter') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF for JS -->
    <meta name="csrf-token-name" content="<?= esc(csrf_token()) ?>">
    <meta name="csrf-token-value" content="<?= esc(csrf_hash()) ?>">

    <!-- Volt Lite CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/volt/css/volt.css') ?>">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>">

    <!-- Your custom overrides -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">

    <?= $this->renderSection('styles') ?>
</head>

<body class="bg-soft">

    <!-- Top Navbar -->
    <?= $this->include('partials/navbar') ?>

    <div class="d-flex">

        <!-- Sidebar -->
        <?= $this->include('partials/sidebar') ?>

        <!-- Main Content Area -->
        <main class="content flex-grow-1 p-3 p-md-4" style="min-height: 100vh; overflow-x: hidden;">

            <!-- Page Header: Title + Subtitle + Actions -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <span class="fas fa-bullhorn me-1"></span>
                    <strong>Holy guacamole!</strong> This system will automatically reset every 3 hours.
                    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <div>
                    <?php if ($this->renderSection('pageTitle')): ?>
                    <h1 class="h3 mb-1"><?= $this->renderSection('pageTitle') ?></h1>
                    <?php elseif (! empty($pageTitle ?? '')): ?>
                    <h1 class="h3 mb-1"><?= esc($pageTitle) ?></h1>
                    <?php endif; ?>

                    <?php if ($this->renderSection('pageSubtitle')): ?>
                    <p class="text-muted small mb-0">
                        <?= $this->renderSection('pageSubtitle') ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Right-side header actions (buttons, filters, etc.) -->
                <div class="d-none d-md-flex align-items-center">
                    <?= $this->renderSection('pageActions') ?>
                </div>

            </div>

            <!-- Main View Content -->
            <?= $this->renderSection('content') ?>

            <!-- Footer -->
            <footer class="bg-white rounded shadow p-5 mb-4 mt-4">
                <div class="row">
                    <div class="col-12 col-md-4 col-xl-6 mb-4 mb-md-0">
                        <p class="mb-0 text-center text-lg-start"><span>&copy; <?= date('Y') ?> <?= esc(setting('App.appName') ?? 'CI4 Starter') ?></span></p>
                    </div>
                    <div class="col-12 col-md-8 col-xl-6 text-center text-lg-start">
                        <!-- List -->
                        <ul class="list-inline list-group-flush list-group-borderless text-md-end mb-0">
                            <li class="list-inline-item px-0 px-sm-2">Powered by CodeIgniter 4 & Volt Lite UI</li>
                        </ul>
                    </div>
                </div>
            </footer>


        </main>
    </div>
    <!-- Global JS Object -->
    <script>
    window.site = window.site || {};
    site.base_url = '<?= rtrim(site_url(), '/') ?>/';
    site.csrfName = '<?= esc(csrf_token()) ?>';
    site.csrfHash = '<?= esc(csrf_hash()) ?>';
    site.userId = '<?= esc(auth()->user()->id ?? '') ?>';
    window.appIdleConfig = {
        timeoutMinutes: <?= esc(setting('Site.idleTimeoutMinutes') ?? 3) ?>,
        pingUrl: "<?= site_url('auth/ping') ?>",
        logoutUrl: "<?= site_url('auth/force-logout') ?>",
    };
    </script>
    <!-- JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    <script src="<?= base_url('assets/js/idle-timeout.js') ?>"></script>
    <?php if (session()->getFlashdata('accessDenied')): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Access Denied',
            text: '<?= esc(session('accessDenied')) ?>',
            confirmButtonText: 'OK'
        });
    });
    </script>
    <?php endif; ?>
    <?= $this->renderSection('scripts') ?>
</body>

</html>