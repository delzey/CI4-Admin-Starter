<div class="modal fade" id="menuModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="menuForm" class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title" id="menuModalLabel">
                    <i class="fas fa-sitemap me-2"></i> Add Menu
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="menu_id" name="id">

                <div class="mb-3">
                    <label class="form-label small">Title</label>
                    <input type="text" name="title" id="title"
                           class="form-control form-control-sm" required>
                </div>

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="category_id" class="form-label small">Category</label>
                        <select id="category_id" name="category_id"
                                class="form-select form-select-sm">
                            <option value="">— None —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat['id']) ?>">
                                    <?= esc($cat['menu_category']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="parent_id" class="form-label small">Parent Menu</label>
                        <select id="parent_id" name="parent_id"
                                class="form-select form-select-sm">
                            <option value="">— None —</option>
                            <?php foreach ($menus as $m): ?>
                                <option value="<?= esc($m['id']) ?>">
                                    <?= esc($m['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label small">Route</label>
                    <input type="text" name="route" id="route"
                           class="form-control form-control-sm">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Icon</label>
                    <input type="text" name="icon" id="icon"
                           class="form-control form-control-sm"
                           placeholder="fa fa-home">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Permission</label>
                    <input type="text" name="permission" id="permission"
                           class="form-control form-control-sm">
                </div>

                <div class="mb-3">
                    <label class="form-label small">Position</label>
                    <input type="number" name="position" id="position"
                           class="form-control form-control-sm" value="0">
                </div>

                <div class="form-check form-switch mb-1">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                    <label class="form-check-label small" for="is_active">Active</label>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit"
                        class="btn btn-primary btn-sm">
                    Save
                </button>
            </div>

        </form>
    </div>
</div>
