<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center">
            <i class="fa fa-database me-2"></i>
            <h5 class="mb-0">Secure Files &amp; Directories</h5>
        </div>
        <div class="card-body">
            <p class="text-muted mb-4">
                Use the buttons below to encrypt and secure your application directories using Bolt.
                Encrypted files will be written under the <code>/writable/encrypted</code> folder.
            </p>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <button type="button"
                                class="btn btn-danger w-100 bolt-action"
                                data-target="controllers">
                            Secure Controllers
                        </button>
                        <div class="ms-2 bolt-status small text-muted"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <button type="button"
                                class="btn btn-danger w-100 bolt-action"
                                data-target="services">
                            Secure Services
                        </button>
                        <div class="ms-2 bolt-status small text-muted"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <button type="button"
                                class="btn btn-danger w-100 bolt-action"
                                data-target="helpers">
                            Secure Helpers
                        </button>
                        <div class="ms-2 bolt-status small text-muted"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <button type="button"
                                class="btn btn-danger w-100 bolt-action"
                                data-target="libraries">
                            Secure Libraries
                        </button>
                        <div class="ms-2 bolt-status small text-muted"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <button type="button"
                                class="btn btn-danger w-100 bolt-action"
                                data-target="models">
                            Secure Models
                        </button>
                        <div class="ms-2 bolt-status small text-muted"></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="d-flex align-items-center">
                        <button type="button"
                                class="btn btn-danger w-100 bolt-action"
                                data-target="commands">
                            Secure Commands
                        </button>
                        <div class="ms-2 bolt-status small text-muted"></div>
                    </div>
                </div>
            </div>

            <p class="mt-3 small text-info">
                <b>Note:</b> <code>This only writes encrypted copies. You still control if/when your app
                starts using those encrypted files (e.g., via custom autoloader or deployment script).</code>
            </p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


<!-- PAGE JS -->
<?= $this->section('scripts') ?>
<?php
    $csrfName = csrf_token();
    $csrfHash = csrf_hash();
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfName = '<?= $csrfName ?>';
    const csrfHash = '<?= $csrfHash ?>';

    document.querySelectorAll('.bolt-action').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            const target = this.dataset.target;
            const row    = this.closest('.d-flex');
            const status = row.querySelector('.bolt-status');
            const label  = target.charAt(0).toUpperCase() + target.slice(1);

            Swal.fire({
                title: 'Secure ' + label + '?',
                text: 'This will create encrypted copies of your ' + label + ' directory.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, secure it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (!result.isConfirmed) return;

                btn.disabled = true;
                status.innerHTML = '<i class="fa fa-sync fa-spin"></i>';

                $.ajax({
                    url: '<?= site_url('bolt/encrypt') ?>/' + encodeURIComponent(target),
                    method: 'POST',
                    dataType: 'json',
                    data: (function () {
                        const payload = {};
                        payload[csrfName] = csrfHash;
                        return payload;
                    })(),
                    success: function (res) {
                        if (res.status === 'ok') {
                            status.innerHTML = '<i class="fa fa-check text-success"></i>';
                            Swal.fire('Done!', res.message, 'success');
                        } else {
                            status.innerHTML = '<i class="fa fa-times text-danger"></i>';
                            Swal.fire('Error', res.message || 'Encryption failed.', 'error');
                            btn.disabled = false;
                        }
                    },
                    error: function (xhr) {
                        console.error('Bolt AJAX error:', xhr.status, xhr.responseText);

                        let msg = 'Server responded with an error.';
                        try {
                            const res = xhr.responseJSON || JSON.parse(xhr.responseText);
                            if (res && res.message) msg = res.message;
                        } catch (e) {}

                        status.innerHTML = '<i class="fa fa-times text-danger"></i>';
                        Swal.fire('Error', msg, 'error');
                        btn.disabled = false;
                    }
                });
            });
        });
    });
});
</script>
<?= $this->endSection() ?>
