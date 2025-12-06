<?php // app/Views/widgets/users/modal_user_groups.php ?>
<div class="modal fade" id="modalUserGroups" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form id="formUserGroups" class="modal-content border-0 shadow">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users-cog me-2"></i> Assign Groups
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="ug_user_id" name="id">

                <div id="ug_groupList" class="row g-2 small">
                    <!-- JS populates checkboxes here -->
                </div>

            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>

        </form>
    </div>
</div>
