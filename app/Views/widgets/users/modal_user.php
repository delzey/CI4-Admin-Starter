<?php // app/Views/widgets/users/modal_user.php ?>
<div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="formUser" class="modal-content border-0 shadow">

      <div class="modal-header border-0 pb-0">
        <div>
          <h5 class="modal-title mb-0">
            <i class="fas fa-user me-2"></i> User
          </h5>
          <small class="text-muted">Create or update a user account</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body pt-3">

        <input type="hidden" name="id" id="user_id">

        <div class="mb-3">
          <label class="form-label small mb-1">Username</label>
          <input class="form-control form-control-sm" name="username" id="username" required autocomplete="off">
        </div>

        <div class="mb-3">
          <label class="form-label small mb-1">
            Password <span class="text-muted">(leave blank to keep)</span>
          </label>
          <input type="password" class="form-control form-control-sm" id="password" name="password" autocomplete="new-password">
        </div>

        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="active" name="active">
          <label class="form-check-label small" for="active">Active</label>
        </div>

      </div>

      <div class="modal-footer border-0 pt-0">
        <button type="submit" class="btn btn-primary btn-sm">
          <i class="fas fa-save me-1"></i> Save
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
      </div>

    </form>
  </div>
</div>
