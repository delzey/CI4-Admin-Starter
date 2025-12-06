<?= $this->extend('layouts/main') ?>

<?= $this->section('pageTitle') ?>
<i class="fa fa-users me-2"></i> User Management
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row">

    <!-- USERS TABLE -->
    <div class="col-lg-8 mb-3">
        <div class="card border-0 shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Users</h6>
                <button class="btn btn-primary btn-sm" id="btnNewUser">
                    <i class="fas fa-plus me-1"></i> New User
                </button>
            </div>

            <div class="card-body p-0">

                <!-- Volt requires a .table-responsive wrapper -->
                <div class="table-responsive">
                    <table id="tblUsers" class="table table-flush table-hover align-middle w-100">
                        <thead class="thead-light">
                            <tr>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Groups</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>

    <!-- GROUP LIST -->
    <div class="col-lg-4 mb-3">
        <div class="card border-0 shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Groups</h6>
                <span class="text-muted small">from AuthGroups</span>
            </div>

            <div class="card-body">
                <ul id="listGroups" class="list-group small"></ul>
            </div>

        </div>
    </div>

</div>

<!-- Modals -->
<?= $this->include('widgets/users/modal_user') ?>
<?= $this->include('widgets/users/modal_user_groups') ?>

<?= $this->endSection() ?>


<!-- PAGE JS -->
<?= $this->section('scripts') ?>

<script>
window.site = {
    base_url: "<?= site_url() ?>/",
    csrfName: "<?= csrf_token() ?>",
    csrfHash: "<?= csrf_hash() ?>",
    canManageAdmins: <?= auth()->user()->can('users.manage-admins') ? 'true' : 'false' ?>
};
</script>

<script src="<?= base_url('assets/js/pages/users.js') ?>"></script>
<?= $this->endSection() ?>