$(function () {
  'use strict';

  let configGroups = [];
  let selectedUserId = null;

  // Holds our current "Active/Inactive" filter function so "All" can remove it
  let usersActiveFilterFn = null;

  // --- tiny helpers ---------------------------------------------------------
  function swalSuccess(msg) {
    Swal.fire({
      toast: true,
      icon: 'success',
      title: msg,
      position: 'top-end',
      showConfirmButton: false,
      timer: 1800,
    });
  }

  function swalError(msg) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: msg || 'An unexpected error occurred.',
    });
  }

  function userInitials(name) {
    if (!name) return '?';
    return String(name).substring(0, 2).toUpperCase();
  }

  function avatarBadge(name) {
    const initials = userInitials(name);
    return `
      <div class="avatar avatar-sm rounded-circle bg-gray-300 text-dark fw-bold d-inline-flex align-items-center justify-content-center">
        ${initials}
      </div>
    `;
  }

  // --- Groups (sidebar + inline checkboxes) --------------------------------
  function loadConfigGroups(cb) {
    $.get('users/groups/list', function (res) {
      configGroups = res || [];

      const ul = $('#listGroups').empty();
      configGroups.forEach((g) => {
        ul.append(`
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>${g.title}</strong> <span class="text-muted">(${g.alias})</span></span>
            <span class="text-muted small">${g.description || ''}</span>
          </li>
        `);
      });

      const inline = $('#userGroupsInline').empty();
      configGroups.forEach((g) => {
        inline.append(`
          <div class="col-6">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="g-${g.alias}" name="groups[]" value="${g.alias}">
              <label class="form-check-label" for="g-${g.alias}">${g.title}</label>
            </div>
          </div>
        `);
      });

      if (typeof cb === 'function') cb();
    });
  }

  // --- DataTable ------------------------------------------------------------
  const tbl = $('#tblUsers').DataTable({
    ajax: 'users/list',
    dom: 'rt',
    pageLength: 10,
    order: [[3, 'desc']],
    columns: [
      {
        data: 'username',
        render: (username, type, row) => `
          <div class="d-flex align-items-center">
            ${avatarBadge(username)}
            <div class="ms-3">
              <h6 class="mb-0 fw-semibold">${username}</h6>
              <small class="text-muted">ID: ${row.id}</small>
            </div>
          </div>
        `,
      },
      {
        data: 'active',
        render: (d) =>
          d
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-gray-400 text-dark">Inactive</span>',
      },
      {
        data: 'groups',
        render: (arr) =>
          (arr || [])
            .map((g) => `<span class="badge bg-gray-600 text-dark border me-1">${g}</span>`)
            .join(''),
      },
      {
        data: 'created_at',
        render: (d) => (d ? `<span class="text-muted small">${formatDateBySetting(d)}</span>` : ''),
      },
      {
        data: 'id',
        orderable: false,
        className: 'text-end',
        render: (id) => `
          <div class="dropdown dropstart">
            <button class="btn btn-sm btn-outline-gray-400 dropdown-toggle" data-bs-toggle="dropdown" type="button">
              <i class="fas fa-ellipsis-h"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm dropdown-menu-fixed">
              <a class="dropdown-item btnEdit" href="#" data-id="${id}">
                <i class="fas fa-edit me-2"></i>Edit
              </a>
              <a class="dropdown-item btnAssignGroups" href="#" data-id="${id}">
                <i class="fas fa-users-cog me-2"></i>Groups
              </a>
              <a class="dropdown-item btnViewPerms" href="#" data-id="${id}">
                <i class="fas fa-user-shield me-2"></i>Permissions
              </a>
              <div class="dropdown-divider"></div>
              <a class="dropdown-item text-danger btnDel" href="#" data-id="${id}">
                <i class="fas fa-trash me-2"></i>Delete
              </a>
            </div>
          </div>
        `,
      },
    ],
  });

  // Fix dropdown positioning inside DataTables (keeps menu from clipping)
  $(document).on('show.bs.dropdown', '.dropdown', function () {
    const $menu = $(this).find('.dropdown-menu-fixed');
    if (!$menu.length) return;

    const rect = this.getBoundingClientRect();
    $menu.css({
      top: rect.top + window.scrollY + 'px',
      left: rect.left + window.scrollX - $menu.outerWidth() + 36 + 'px',
    });
  });

  // User Status dropdown -> filter by raw active flag (1 / 0)
  $("#userStatusFilter").on("change", function () {
    const status = String(this.value ?? "");

    // Clear table searches (keeps behavior consistent)
    tbl.search("");
    tbl.columns().search("");

    // Remove existing active filter if present
    if (usersActiveFilterFn) {
      $.fn.dataTable.ext.search =
        $.fn.dataTable.ext.search.filter(fn => fn !== usersActiveFilterFn);
      usersActiveFilterFn = null;
    }

    // ALL
    if (status === "") {
      tbl.draw();
      return;
    }

    // Add new filter
    usersActiveFilterFn = function (settings, data, dataIndex) {
      if (!settings.nTable || settings.nTable.id !== "tblUsers") return true;

      const row = tbl.row(dataIndex).data();
      const isActive =
        row && (row.active === 1 || row.active === "1" || row.active === true);

      if (status === "active") return isActive;
      if (status === "inactive") return !isActive;

      return true;
    };

    $.fn.dataTable.ext.search.push(usersActiveFilterFn);
    tbl.draw();
  });

  // --- External table controls ---------------------------------------------
  $('#usersSearch').on('keyup', function () {
    tbl.search(this.value).draw();
  });

  $('#usersPerPage').on('change', function () {
    tbl.page.len(this.value).draw();
  });

  // --- New User -------------------------------------------------------------
  $('#btnNewUser').on('click', function () {
    $('#formUser')[0].reset();
    $('#user_id').val('');
    $('#userGroupsInline input[type=checkbox]').prop('checked', false);
    new bootstrap.Modal('#modalUser').show();
  });

  // --- Save User ------------------------------------------------------------
  $('#formUser').on('submit', function (e) {
    e.preventDefault();

    const active = $('#active').is(':checked') ? 1 : 0;
    const formData = $(this).serializeArray();
    formData.push({ name: 'active', value: active });

    const endpoint = $('#user_id').val() ? 'users/update' : 'users/create';

    $.post(endpoint, $.param(formData), function (res) {
      if (res?.success) {
        swalSuccess('User saved successfully');
        bootstrap.Modal.getInstance(document.getElementById('modalUser')).hide();
        tbl.ajax.reload(null, false);
      } else {
        swalError(res?.message);
      }
    });
  });

  // --- Edit User ------------------------------------------------------------
  $('#tblUsers').on('click', '.btnEdit', function (e) {
    e.preventDefault();

    const row = tbl.row($(this).closest('tr')).data();
    if (!row) return;

    $('#user_id').val(row.id);
    $('#username').val(row.username);
    $('#active').prop('checked', !!row.active);
    $('#password').val('');
    $('#userGroupsInline input[type=checkbox]').prop('checked', false);

    (row.groups || []).forEach((g) => {
      $("#userGroupsInline input[value='" + g + "']").prop('checked', true);
    });

    new bootstrap.Modal('#modalUser').show();
  });

  // --- Delete User ----------------------------------------------------------
  $('#tblUsers').on('click', '.btnDel', function (e) {
    e.preventDefault();

    const id = $(this).data('id');

    Swal.fire({
      title: 'Delete User?',
      text: 'This action cannot be undone.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Delete',
      reverseButtons: true,
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.post('users/delete', { id }, function (res) {
        if (res?.success) {
          swalSuccess('User deleted');
          tbl.ajax.reload(null, false);
        } else {
          swalError(res?.message);
        }
      });
    });
  });

  // --- Assign Groups --------------------------------------------------------
  $('#tblUsers').on('click', '.btnAssignGroups', function (e) {
    e.preventDefault();

    selectedUserId = $(this).data('id');
    $('#ug_user_id').val(selectedUserId);

    const holder = $('#ug_groupList').empty();
    configGroups.forEach((g) => {
      holder.append(`
        <div class="col-6">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="ug-${g.alias}" name="groups[]" value="${g.alias}">
            <label class="form-check-label" for="ug-${g.alias}">${g.title}</label>
          </div>
        </div>
      `);
    });

    $.get('users/user-groups', { id: selectedUserId }, function (res) {
      (res.groups || []).forEach((alias) => {
        $(`#ug_groupList input[value='${alias}']`).prop('checked', true);
      });

      new bootstrap.Modal('#modalUserGroups').show();
    });
  });

  // Save group assignment
  $('#formUserGroups').on('submit', function (e) {
    e.preventDefault();

    $.post('users/user-groups/set', $(this).serialize(), function (res) {
      if (res?.success) {
        swalSuccess('Groups updated');
        bootstrap.Modal.getInstance(document.getElementById('modalUserGroups')).hide();
        tbl.ajax.reload(null, false);
      } else {
        swalError(res?.message);
      }
    });
  });

  // --- Permissions modal ----------------------------------------------------
  const permModalEl = document.getElementById('permModal');
  const permModalBody = document.getElementById('permModalBody');
  const permModal = permModalEl ? new bootstrap.Modal(permModalEl) : null;

  $('#tblUsers').on('click', '.btnViewPerms', function (e) {
    e.preventDefault();
    if (!permModal) return;

    const id = $(this).data('id');
    permModalBody.innerHTML = '<div class="text-center text-muted p-4">Loading...</div>';
    permModal.show();

    fetch(`/auth-permissions/table/${id}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((res) => res.text())
      .then((html) => {
        permModalBody.innerHTML = html;
        bindPermToggles();
      })
      .catch(() => {
        permModalBody.innerHTML =
          '<div class="alert alert-danger mb-0">Failed to load permissions.</div>';
      });
  });

  function bindPermToggles() {
    if (!permModalBody) return;

    permModalBody.querySelectorAll('.tglPerm').forEach((cb) => {
      cb.addEventListener(
        'change',
        () => {
          const data = new URLSearchParams({
            user_id: cb.dataset.user,
            module: cb.dataset.module,
            operation: cb.dataset.operation,
            active: cb.checked ? '1' : '0',
          });

          fetch('/auth-permissions/chgPerm', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: data,
          })
            .then((r) => r.json())
            .then((json) => {
              Swal.fire({
                toast: true,
                position: 'top-end',
                icon: json.success ? 'success' : 'error',
                title: json.message,
                showConfirmButton: false,
                timer: 1500,
              });
            })
            .catch(() => Swal.fire('Error', 'Failed to update permission', 'error'));
        },
        { once: true }
      );
    });
  }

  // --- Init -----------------------------------------------------------------
  loadConfigGroups();
});