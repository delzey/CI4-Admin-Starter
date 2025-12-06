<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= esc(setting('Site.siteName') ?? 'Login') ?> — Sign In</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Volt Lite CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/volt/css/volt.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>">

    <style>
        body {
            background-color: #f5f8fb;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center vh-100 bg-soft">

    <main class="w-100">

        <div class="container">
            <div class="row justify-content-center">
                <div class="col-sm-10 col-md-6 col-lg-4">

                    <div class="bg-white shadow border-0 rounded border-light p-4 p-lg-5 w-100 fmxw-500">

                        <!-- App Logo or Title -->
                        <div class="text-center mb-4">
                            <h3 class="fw-bold mb-0">
                                <i class="fas fa-plane me-2 text-primary"></i>
                                <?= esc(setting('App.appName') ?? 'Application') ?>
                            </h3>
                            <p class="text-muted small mt-1">Please sign in to continue</p>
                        </div>

                        <?php if (session('error')): ?>
                            <div class="alert alert-danger small">
                                <?= session('error') ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?= site_url('login') ?>" method="post" class="mt-3">
                            <?= csrf_field() ?>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label small">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-user text-secondary"></i>
                                    </span>
                                    <input
                                        type="email"
                                        class="form-control"
                                        id="email"
                                        name="email"
                                        value="<?= old('email') ?>"
                                        required
                                        autofocus
                                    >
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label small">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-lock text-secondary"></i>
                                    </span>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        required
                                    >
                                </div>
                            </div>

                            <!-- Remember + Forgot -->
                            <div class="d-flex justify-content-between align-items-center mb-3 small">
                                <div class="form-check m-0">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>

                                <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                                    <a href="<?= url_to('magic-link') ?>" class="small text-decoration-none"><?= lang('Auth.useMagicLink') ?></a>
                                <?php endif ?>
                            </div>

                            <!-- Submit -->
                            <button class="btn btn-primary w-100" type="submit">
                                Sign In
                            </button>

                        </form>
                        <?php if (setting('Auth.allowRegistration')) : ?>
                        <div class="d-flex justify-content-center align-items-center mt-4">
                            <span class="fw-normal"> 
                                <a href="<?= url_to('register') ?>" class="small text-decoration-none">Click here to <?= lang('Auth.register') ?>
                            </span>
                        </div>
                        <?php endif ?>
                    </div>

                </div>
            </div>
        </div>

    </main>

    <!-- Required JS (Bootstrap only) -->
    <script src="<?= base_url('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>

</body>
</html>
