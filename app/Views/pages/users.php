<?= $this->extend('layouts/main') ?>
<?= $this->section('pageTitle') ?>
<i class="fa fa-users me-2"></i> Users List
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="card shadow-sm border-0">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users me-2"></i> Users List</span>
        <button class="btn btn-sm btn-primary d-inline-flexs align-items-center me-2" id="btnNewUser" type="button">
            <i class="fas fa-plus me-2"></i> New User
        </button>
    </div>
    <div class="card-body p-0">
        <div class="row">
            <div class="col-12">
                <div class="row p-2">
                    <div class="d-flex mb-3">
                        <select class="form-select form-select-sm fmxw-200" id="userStatusFilter" aria-label="User Status">
                            <option value="" disabled selected>Select status</option>
                            <option value="" selected>All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <!-- USERS TABLE -->
                    <div class="col-lg-8 mb-3">
                        <div class="card border-1 shadow-sm">
                            <div class="card-body p-2">
                                <div class="table">
                                    <table id="tblUsers" class="table table-hover align-middle mb-0 w-100">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Username</th>
                                                <th>Status</th>
                                                <th>Groups</th>
                                                <th>Created</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <!-- Footer meta (optional; DataTables can replace this) -->
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                                    <small class="text-muted" id="usersTableMeta"> </small>
                                    <div id="usersPagerSlot"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- GROUP LIST -->
                    <div class="col-lg-4 mb-3">
                        <div class="card border-1 shadow-sm">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold">Groups</h6>
                                <span class="text-muted small">from AuthGroups</span>
                            </div>
                            <div class="card-body">
                                <ul id="listGroups" class="list-group small"></ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modals -->
<?= $this->include('widgets/users/modal_user') ?>
<?= $this->include('widgets/users/modal_user_groups') ?>
<?= $this->include('widgets/users/modal_user_permissions') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.site = {
    base_url: "<?= site_url() ?>/",
    csrfName: "<?= csrf_token() ?>",
    csrfHash: "<?= csrf_hash() ?>",
    canManageAdmins: <?= auth()->user()->can('users.manage-admins') ? 'true' : 'false' ?>
};
</script>
<script src="<?= asset('app/js/pages/users.js') ?>"></script>

<!-- OPTIONAL: lightweight hooks (won't break anything if users.js ignores them) -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Tabs (visual match). If your users.js supports it later, you can read `data-status`.
    document.querySelectorAll('#userStatusTabs a[data-status]').forEach(a => {
        a.addEventListener('click', () => {
            document.querySelectorAll('#userStatusTabs .nav-link').forEach(x => x.classList.remove('active'));
            a.classList.add('active');
        });
    });
});
</script>
<?= $this->endSection() ?>