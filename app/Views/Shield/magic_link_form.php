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


<section class="vh-lg-100 mt-5 mt-lg-0 bg-soft d-flex align-items-center">
    <div class="container">
        <div class="row justify-content-center form-bg-image">

            <!-- Back to Login -->
            <p class="text-center">
                <a href="<?= url_to('login') ?>" class="d-flex align-items-center justify-content-center text-decoration-none">
                    <svg class="icon icon-xs me-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.4140l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.4141.414L5.414 9H17a1 1 0 1102H5.414l2.2932.293a1 1 0 0101.414z"/>
                    </svg>
                    Back to Login
                </a>
            </p>

            <div class="col-12 d-flex align-items-center justify-content-center">
                <div class="signin-inner bg-white shadow border-0 rounded p-4 p-lg-5 w-100 fmxw-500">

                    <h1 class="h3 mb-3">Magic Link Login</h1>
                    <p class="mb-4 text-muted">Enter your email and we’ll send you a one-time login link.</p>

                    <?php if(session('error')): ?>
                        <div class="alert alert-danger"><?= esc(session('error')) ?></div>
                    <?php endif; ?>

                    <?php if(session('errors')): ?>
                        <div class="alert alert-danger">
                            <?php foreach((array)session('errors') as $e): ?>
                                <?= esc($e) ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= url_to('magic-link') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label for="email" class="form-label">Your Email</label>
                            <div class="input-group">
                                <input type="email"
                                       name="email"
                                       id="email"
                                       class="form-control"
                                       placeholder="you@company.com"
                                       required
                                       value="<?= old('email', auth()->user()->email ?? '') ?>">
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-gray-800">
                                Send Magic Link
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</section>
    <!-- Required JS (Bootstrap only) -->
    <script src="<?= base_url('assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>

</body>
</html>
