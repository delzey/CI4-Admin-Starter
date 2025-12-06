<?php // app/Views/widgets/users/modal_user.php ?>
<div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="formUser" class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user me-2"></i> User
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" name="id" id="user_id">

                <div class="mb-3">
                    <label class="form-label small">Username</label>
                    <input class="form-control form-control-sm"
                           name="username"
                           id="username"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label small">
                        Password <span class="text-muted">(leave blank to keep)</span>
                    </label>
                    <input type="password"
                           class="form-control form-control-sm"
                           id="password"
                           name="password">
                </div>

                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="active" name="active">
                    <label class="form-check-label small" for="active">Active</label>
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>

        </form>
    </div>
</div>
