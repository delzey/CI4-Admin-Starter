<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Register — <?= esc(setting('App.appName')) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="<?= base_url('assets/volt/css/volt.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>" rel="stylesheet">
</head>

<body class="bg-soft d-flex align-items-center justify-content-center vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <h4 class="text-center fw-semibold mb-3">
                        <i class="fas fa-user-plus me-2"></i> Create Account
                    </h4>

                    <?php if (session('error')): ?>
                        <div class="alert alert-danger small"><?= session('error') ?></div>
                    <?php endif; ?>

                    <form action="<?= url_to('register') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label small">Email</label>
                            <input class="form-control form-control-sm"
                                   name="email"
                                   value="<?= old('email') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Username</label>
                            <input class="form-control form-control-sm"
                                   name="username"
                                   value="<?= old('username') ?>"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small">Password</label>
                            <input type="password"
                                   class="form-control form-control-sm"
                                   name="password"
                                   required>
                        </div>

                        <button class="btn btn-primary w-100">Register</button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
