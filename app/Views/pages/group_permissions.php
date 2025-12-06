<?= $this->extend('layouts/main') ?>

<!-- Page Title -->
<?= $this->section('pageTitle') ?>
<i class="fa fa-lock me-2"></i> Group Permissions
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row">
    <!-- LEFT SIDE — GROUP LIST -->
    <div class="col-lg-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Groups</h6>
                <span class="text-white-50 small">from AuthGroups</span>
            </div>

            <div class="card-body p-0">
                <ul id="gp_groupList" class="list-group list-group-flush small">
                    <!-- JS populates -->
                </ul>
            </div>
        </div>
    </div>
    <!-- RIGHT SIDE — PERMISSION GRID -->
    <div class="col-lg-8 mb-3">
        <div class="card border-0 shadow-sm">

            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <h6 class="mb-0 fw-semibold">Permissions</h6>
                    <span id="gp_currentGroupLabel" class="text-white-50 ms-2 small"></span>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-sm" id="gp_btnSelectAll" disabled>
                        Select All
                    </button>
                    <button class="btn btn-outline-light btn-sm" id="gp_btnClearAll" disabled>
                        Clear All
                    </button>
                    <button class="btn btn-light btn-sm" id="gp_btnSave" disabled>
                        Save
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Permission checkbox grid (built by JS) -->
                <div id="gp_permContainer" class="small">
                    <!-- JS injects grouped permissions -->
                </div>
                <p class="text-muted small mt-3">
                    Only permissions defined in <code>Config\AuthGroups::$permissions</code> can be assigned.
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<!-- PAGE JS -->
<?= $this->section('scripts') ?>

<script>
window.appConfig = {
    baseUrl: "<?= rtrim(site_url(), '/') ?>",
    csrf: {
        token: "<?= esc(csrf_token()) ?>",
        hash: "<?= esc(csrf_hash()) ?>",
    }
};
</script>

<script src="<?= base_url('assets/js/pages/group_permissions.js') ?>"></script>
<?= $this->endSection() ?>