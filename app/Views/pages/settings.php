<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Application Settings</h3>
    <button id="btnSave" class="btn btn-primary btn-sm">Save</button>
</div>

<form id="settingsForm" class="card card-body">
    <div class="mb-3">
        <label class="form-label">Theme</label>
        <select class="form-select" name="app_theme" id="app_theme">
            <option value="light">Light</option>
            <option value="dark">Dark</option>
        </select>
    </div>

    <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" id="app_sidebar_collapsed" name="app_sidebar_collapsed">
        <label class="form-check-label" for="app_sidebar_collapsed">Sidebar Collapsed (Default)</label>
    </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/settings.js'); ?>"></script>

<?= $this->endSection() ?>
