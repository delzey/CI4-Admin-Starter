<?= $this->extend('layouts/main') ?>

<?= $this->section('pageTitle') ?>
<i class="fas fa-sitemap me-2"></i> Menu Management
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-4">

    <!-- ======================================================
         LEFT PANEL – CATEGORIES
    ======================================================= -->
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Categories</strong>
                <button class="btn btn-sm btn-primary" id="btnAddCategory">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <ul class="list-group list-group-flush" id="categoryList">
                <?php foreach ($categories as $cat): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center small"
                        data-id="<?= esc($cat['id']) ?>">

                        <span class="cat-label">
                            <?= esc($cat['menu_category']) ?>
                        </span>

                        <span class="cat-actions">

                            <button class="btn btn-sm btn-warning btn-cat-edit" data-id="<?= esc($cat['id']) ?>">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="btn btn-sm btn-danger btn-cat-delete" data-id="<?= esc($cat['id']) ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </span>

                    </li>
                <?php endforeach; ?>
            </ul>

        </div>
    </div>

    <!-- ======================================================
         RIGHT PANEL – MENU TREE
    ======================================================= -->
    <div class="col-md-9">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Menu Structure</strong>
                <button id="btnAddMenuTop" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="card-body" id="menuTreeContainer">
                <div class="text-muted text-center py-4">Loading menu structure...</div>
            </div>
        </div>
    </div>

</div>

<?= $this->include('widgets/menu/menu_modal') ?>
<?= $this->include('widgets/menu/category_modal') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= asset('app/js/menu_management_tree.js') ?>"></script>
<?= $this->endSection() ?>