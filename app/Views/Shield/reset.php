<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Reset Password — <?= esc(setting('App.appName')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?= base_url('assets/volt/css/volt.css') ?>" rel="stylesheet">
</head>

<body class="bg-soft d-flex align-items-center justify-content-center vh-100">

<div class="col-md-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <h4 class="fw-semibold text-center mb-3">
                <i class="fas fa-key me-2"></i> Reset Password
            </h4>

            <?php if (session('error')): ?>
                <div class="alert alert-danger small"><?= session('error') ?></div>
            <?php endif; ?>

            <form action="<?= current_url() ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small">New Password</label>
                    <input type="password"
                           class="form-control form-control-sm"
                           name="password"
                           required>
                </div>

                <button class="btn btn-primary w-100" type="submit">Update Password</button>
            </form>

        </div>
    </div>
</div>

</body>
</html>
