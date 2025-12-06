<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="categoryForm" class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">
                    <i class="fas fa-folder-plus me-2"></i> Add Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="category_id" name="id">

                <div class="mb-3">
                    <label class="form-label small">Category Name</label>
                    <input type="text"
                           class="form-control form-control-sm"
                           id="category_name"
                           name="menu_category"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Position</label>
                    <input type="number"
                           class="form-control form-control-sm"
                           id="category_position"
                           name="position"
                           value="0">
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary btn-sm">
                    Save Category
                </button>
            </div>

        </form>
    </div>
</div>
