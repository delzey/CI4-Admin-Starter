<?= $this->extend('layouts/main') ?>

<!-- Page Title -->
<?= $this->section('pageTitle') ?>
<i class="fas fa-user-shield me-2"></i> Permissions Manager
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- MAIN CARD -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <!-- Table wrapper -->
        <div class="table-responsive">
            <table class="table table-flush align-middle">
                <thead class="thead-light">
                    <tr>
                        <th width="5%">#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($u->username ?? '') ?></td>
                            <td><?= esc($u->email ?? '') ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary btnViewPerms"
                                        data-user="<?= esc($u->id) ?>">
                                    <i class="fas fa-shield-alt me-1"></i> Permissions
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- MODAL -->
<div class="modal fade" id="permModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-shield me-2"></i> User Permissions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="permModalBody">
                <div class="text-center text-muted py-5">
                    Select a user to load permissions...
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
<!-- PAGE JS -->
<?= $this->section('scripts') ?>

<script src="<?= base_url('assets/js/permissions.js'); ?>"></script>

<?= $this->endSection() ?>
