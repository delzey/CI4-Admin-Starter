// ========================================================================
// Group Permissions JS (external file)
// ========================================================================

// --- helpers --------------------------------------------------------------
function csrfField() {
    const o = {};
    o[window.appConfig.csrf.token] = window.appConfig.csrf.hash;
    return o;
}

function toast(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 1800,
        timerProgressBar: true,
    });
}

function url(path) {
    return window.appConfig.baseUrl + '/' + path.replace(/^\/+/, '');
}

// --- state ----------------------------------------------------------------
let gp_groups   = {};     // { alias: {title, description, permissions:[...]}, ... }
let gp_permsAll = {};     // { module: { permKey: desc, ... }, ... }
let gp_active   = null;   // currently selected group alias

// --- boot -----------------------------------------------------------------
$(function () {

    $.when(
        $.get(url('auth-groups/groups')),
        $.get(url('auth-groups/permissions'))
    )
    .done(function (g, p) {
        gp_groups   = g[0] || {};
        gp_permsAll = p[0] || {};

        renderGroupList();
        renderPermGrid(); // empty initial display
    })
    .fail(function () {
        toast('Failed to load group and permission data.', 'error');
    });

    // Buttons
    $('#gp_btnSelectAll').on('click', function () {
        $('#gp_permContainer input[type=checkbox]').prop('checked', true);
    });

    $('#gp_btnClearAll').on('click', function () {
        $('#gp_permContainer input[type=checkbox]').prop('checked', false);
    });

    $('#gp_btnSave').on('click', saveGroupPerms);
});

// --- ui: groups ------------------------------------------------------------
function renderGroupList() {
    const ul = $('#gp_groupList').empty();

    Object.entries(gp_groups).forEach(([alias, info]) => {
        const li = $(`
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold">${info.title} <span class="text-muted">(${alias})</span></div>
                    <div class="small text-muted">${info.description || ''}</div>
                </div>
                <button class="btn btn-sm btn-outline-primary">Select</button>
            </li>
        `);

        li.find('button').on('click', () => selectGroup(alias));
        ul.append(li);
    });
}

function selectGroup(alias) {
    gp_active = alias;

    const info = gp_groups[alias] || { title: alias, permissions: [] };

    $('#gp_currentGroupLabel').text(`→ ${info.title} (${alias})`);

    renderPermGrid(info.permissions || []);

    $('#gp_btnSelectAll, #gp_btnClearAll, #gp_btnSave').prop('disabled', false);
}

// --- ui: permission grid ---------------------------------------------------
function renderPermGrid(current = []) {
    const holder = $('#gp_permContainer').empty();

    Object.keys(gp_permsAll).sort().forEach(module => {

        const modBlock = $(`
            <div class="mb-3 border rounded">
                <div class="p-2 bg-light border-bottom d-flex align-items-center justify-content-between">
                    <span class="fw-semibold text-uppercase">${module}</span>
                    <div class="small">
                        <a href="#" class="btn-sm btn-outline-gray-900 me-2 gp_modAll" data-module="${module}">All</a>
                        <a href="#" class="btn-sm btn-outline-gray-900 gp_modNone" data-module="${module}">None</a>
                    </div>
                </div>
                <div class="p-2">
                    <div class="row g-2" data-module="${module}"></div>
                </div>
            </div>
        `);

        const row = modBlock.find('.row');

        Object.entries(gp_permsAll[module]).forEach(([permKey, desc]) => {
            const checked = current.includes(permKey) ? 'checked' : '';

            row.append(`
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="form-check">
                        <input class="form-check-input gp_perm" type="checkbox" id="gp-${permKey}" value="${permKey}" ${checked}>
                        <label class="form-check-label" for="gp-${permKey}">
                            <span class="fw-semibold">${permKey.split('.').pop()}</span>
                            <span class="text-muted small d-block">${desc}</span>
                        </label>
                    </div>
                </div>
            `);
        });

        // module toggles
        modBlock.find('.gp_modAll').on('click', function (e) {
            e.preventDefault();
            const mod = $(this).data('module');
            $(`[data-module="${mod}"] .gp_perm`).prop('checked', true);
        });

        modBlock.find('.gp_modNone').on('click', function (e) {
            e.preventDefault();
            const mod = $(this).data('module');
            $(`[data-module="${mod}"] .gp_perm`).prop('checked', false);
        });

        holder.append(modBlock);
    });

    if (!Object.keys(gp_permsAll).length) {
        holder.html('<div class="text-muted">No permissions defined in Config\\AuthGroups::$permissions.</div>');
    }
}

// --- save ------------------------------------------------------------------
function saveGroupPerms() {

    if (!gp_active) return;

    const picked = $('#gp_permContainer input.gp_perm:checked')
        .map((_, el) => el.value)
        .get();

    const postData = Object.assign({}, csrfField(), {
        group: gp_active,
        'permissions[]': picked
    });

    $.post(url('auth-groups/save'), postData, function (res) {

        if (res && res.success) {
            toast('Permissions saved.', 'success');

            // Optionally refresh merged view
            $.get(url('auth-groups/groups'), function (g) {
                gp_groups = g || gp_groups;
            });

            return;
        }

        toast(res.message || 'Failed to save permissions.', 'error');
    });
}
