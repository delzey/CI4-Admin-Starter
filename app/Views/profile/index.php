<?= $this->extend('layouts/main') ?>

<?= $this->section('pageTitle') ?>
<i class="fas fa-user-circle me-2"></i> My Profile
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-3">

    <!-- LEFT: Profile summary -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center">
                <div class="avatar avatar-xl rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3">
                    <i class="fas fa-user"></i>
                </div>
                <h5 class="fw-semibold mb-0">
                    <?= esc($authUser->username ?? $authUser->email ?? 'User') ?>
                </h5>
                <p class="text-muted small mb-1"><?= esc($authUser->email ?? '') ?></p>

                <span class="badge rounded-pill <?= $profileComplete ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= $profileComplete ? 'Profile Complete' : 'Profile Incomplete' ?>
                </span>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="mb-0 fw-semibold">Account Info</h6>
            </div>
            <div class="card-body small">
                <dl class="row mb-0">
                    <dt class="col-4">Username</dt>
                    <dd class="col-8"><?= esc($authUser->username ?? '—') ?></dd>

                    <dt class="col-4">Email</dt>
                    <dd class="col-8"><?= esc($authUser->email ?? '—') ?></dd>

                    <dt class="col-4">Status</dt>
                    <dd class="col-8">
                        <?= $authUser->active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?>
                    </dd>
                </dl>
                <p class="text-muted mt-2 mb-0 small">
                    Email and username are managed by an administrator.
                </p>
            </div>
        </div>
    </div>

    <!-- RIGHT: Tabs -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header border-bottom-0 pb-0">
                <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-profile-info"
                                data-bs-toggle="tab" data-bs-target="#pane-profile-info"
                                type="button" role="tab">
                            Profile Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-password"
                                data-bs-toggle="tab" data-bs-target="#pane-password"
                                type="button" role="tab">
                            Change Password
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content">

                    <!-- Profile Info -->
                    <div class="tab-pane fade show active" id="pane-profile-info" role="tabpanel">
                        <form id="formProfile">
                            <?= csrf_field() ?>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">First Name</label>
                                    <input type="text" name="firstname" class="form-control form-control-sm"
                                           value="<?= esc($details->firstname ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Middle Name</label>
                                    <input type="text" name="middlename" class="form-control form-control-sm"
                                           value="<?= esc($details->middlename ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">Last Name</label>
                                    <input type="text" name="lastname" class="form-control form-control-sm"
                                           value="<?= esc($details->lastname ?? '') ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small">Phone</label>
                                    <input type="text" name="phone" class="form-control form-control-sm"
                                           value="<?= esc($details->phone ?? '') ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small">Email (read-only)</label>
                                    <input type="text" class="form-control form-control-sm"
                                           value="<?= esc($authUser->email ?? '') ?>" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small">Address 1</label>
                                    <input type="text" name="address1" class="form-control form-control-sm"
                                           value="<?= esc($details->address1 ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Address 2</label>
                                    <input type="text" name="address2" class="form-control form-control-sm"
                                           value="<?= esc($details->address2 ?? '') ?>">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small">City</label>
                                    <input type="text" name="city" class="form-control form-control-sm"
                                           value="<?= esc($details->city ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">State</label>
                                    <input type="text" name="state" class="form-control form-control-sm"
                                           value="<?= esc($details->state ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">ZIP</label>
                                    <input type="text" name="zip" class="form-control form-control-sm"
                                           value="<?= esc($details->zip ?? '') ?>">
                                </div>
                            </div>

                            <div class="mt-3 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Save Profile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Change Password -->
                    <div class="tab-pane fade" id="pane-password" role="tabpanel">
                        <?php if (session('error')): ?>
                            <div class="alert alert-danger small"><?= session('error') ?></div>
                        <?php elseif (session('message')): ?>
                            <div class="alert alert-success small"><?= session('message') ?></div>  
                        <?php endif; ?>

                        <?php $errors = session('errors') ?? []; ?>

                        <form action="<?= site_url('profile/change-password') ?>" method="post" class="mt-2">
                            <?= csrf_field() ?>

                            <div class="mb-3">
                                <label class="form-label small">Current Password</label>
                                <input type="password"
                                       name="current_password"
                                       class="form-control form-control-sm <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>">
                                <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback small">
                                        <?= esc($errors['current_password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small">New Password</label>
                                <input type="password"
                                       name="password"
                                       class="form-control form-control-sm <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback small">
                                        <?= esc($errors['password']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small">Confirm Password</label>
                                <input type="password"
                                       name="password_confirm"
                                       class="form-control form-control-sm <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>">
                                <?php if (isset($errors['password_confirm'])): ?>
                                    <div class="invalid-feedback small">
                                        <?= esc($errors['password_confirm']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-danger btn-sm">
                                Update Password &amp; Log Out
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.site = {
        base_url: "<?= rtrim(site_url(), '/') ?>/",
        csrfName: "<?= csrf_token() ?>",
        csrfHash: "<?= csrf_hash() ?>",
        profileUpdateUrl: "<?= site_url('profile/update') ?>"
    };
</script>
<script src="<?= base_url('assets/js/pages/profile.js') ?>"></script>
<?= $this->endSection() ?>
