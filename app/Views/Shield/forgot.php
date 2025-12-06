<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Forgot Password — <?= esc(setting('App.appName')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?= base_url('assets/volt/css/volt.css') ?>" rel="stylesheet">
</head>

<body class="bg-soft d-flex align-items-center justify-content-center vh-100">

<div class="col-md-4">

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">

            <h4 class="text-center fw-semibold mb-3">
                <i class="fas fa-unlock me-2"></i> Forgot Password
            </h4>

            <?php if (session('error')): ?>
                <div class="alert alert-danger small"><?= session('error') ?></div>
            <?php endif; ?>

            <form action="<?= url_to('forgot') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label small">Email Address</label>
                    <input class="form-control form-control-sm" name="email" required>
                </div>

                <button class="btn btn-primary w-100" type="submit">Send Reset Email</button>
            </form>

        </div>
    </div>

</div>

</body>
</html>
