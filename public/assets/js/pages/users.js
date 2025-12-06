$(function () {
  let configGroups = [];
  let selectedUserId = null;

  // SweetAlert helpers
  function swalSuccess(msg) {
    Swal.fire({
      toast: true,
      icon: "success",
      title: msg,
      position: "top-end",
      showConfirmButton: false,
      timer: 1800,
    });
  }

  function swalError(msg) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: msg || "An unexpected error occurred.",
    });
  }

  // Load groups from config
  function loadConfigGroups(cb) {
    $.get("users/groups/list", function (res) {
      configGroups = res || [];

      const ul = $("#listGroups").empty();
      configGroups.forEach((g) => {
        ul.append(`
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><strong>${g.title}</strong> <span class="text-muted">(${g.alias})</span></span>
            <span class="text-muted small">${g.description || ""}</span>
          </li>
        `);
      });

      const inline = $("#userGroupsInline").empty();
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

      cb && cb();
    });
  }

  // DataTable
  const tbl = $("#tblUsers").DataTable({
    ajax: "users/list",
    dom: "r",
    columns: [
      { data: "username" },
      {
        data: "active",
        render: (d) =>
          d
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>',
      },
      {
        data: "groups",
        render: (arr) =>
          (arr || [])
            .map((g) => `<span class="badge text-bg-light me-1">${g}</span>`)
            .join(""),
      },
      { data: "created_at" },
      {
        data: "id",
        orderable: false,
        render: (id) => `
          <div class="btn-group btn-group-sm">
            <button class="btn btn-info btnEdit" data-id="${id}">Edit</button>
            <button class="btn btn-secondary btnAssignGroups" data-id="${id}">Groups</button>
            <button class="btn btn-danger btnDel" data-id="${id}">Delete</button>
          </div>
        `,
      },
    ],
  });

  // New User
  $("#btnNewUser").on("click", function () {
    $("#formUser")[0].reset();
    $("#user_id").val("");
    $("#userGroupsInline input[type=checkbox]").prop("checked", false);
    new bootstrap.Modal("#modalUser").show();
  });

  // Save User
  $("#formUser").on("submit", function (e) {
    e.preventDefault();

    const active = $("#active").is(":checked") ? 1 : 0;
    const formData = $(this).serializeArray();
    formData.push({ name: "active", value: active });

    const endpoint = $("#user_id").val() ? "users/update" : "users/create";

    $.post(endpoint, $.param(formData), function (res) {
      if (res?.success) {
        swalSuccess("User saved successfully");
        bootstrap.Modal.getInstance(document.getElementById("modalUser")).hide();
        tbl.ajax.reload(null, false);
      } else {
        swalError(res.message);
      }
    });
  });

  // Edit User
  $("#tblUsers").on("click", ".btnEdit", function () {
    const row = tbl.row($(this).closest("tr")).data();

    $("#user_id").val(row.id);
    $("#username").val(row.username);
    $("#active").prop("checked", !!row.active);
    $("#password").val("");
    $("#userGroupsInline input[type=checkbox]").prop("checked", false);

    (row.groups || []).forEach((g) => {
      $("#userGroupsInline input[value='" + g + "']").prop("checked", true);
    });

    new bootstrap.Modal("#modalUser").show();
  });

  // Delete User
  $("#tblUsers").on("click", ".btnDel", function () {
    const id = $(this).data("id");

    Swal.fire({
      title: "Delete User?",
      text: "This action cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Delete",
      reverseButtons: true,
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.post("users/delete", { id }, function (res) {
        if (res?.success) {
          swalSuccess("User deleted");
          tbl.ajax.reload(null, false);
        } else {
          swalError(res.message);
        }
      });
    });
  });

  // Assign Groups
  $("#tblUsers").on("click", ".btnAssignGroups", function () {
    selectedUserId = $(this).data("id");
    $("#ug_user_id").val(selectedUserId);

    const holder = $("#ug_groupList").empty();
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

    $.get("users/user-groups", { id: selectedUserId }, function (res) {
      (res.groups || []).forEach((alias) => {
        $(`#ug_groupList input[value='${alias}']`).prop("checked", true);
      });

      new bootstrap.Modal("#modalUserGroups").show();
    });
  });

  // Save group assignment
  $("#formUserGroups").on("submit", function (e) {
    e.preventDefault();

    $.post("users/user-groups/set", $(this).serialize(), function (res) {
      if (res?.success) {
        swalSuccess("Groups updated");
        bootstrap.Modal.getInstance(document.getElementById("modalUserGroups")).hide();
        tbl.ajax.reload(null, false);
      } else {
        swalError(res.message);
      }
    });
  });

  // Init
  loadConfigGroups();
});
