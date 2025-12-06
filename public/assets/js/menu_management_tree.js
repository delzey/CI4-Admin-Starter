document.addEventListener('DOMContentLoaded', () => {

    // =====================================================================
    // REFERENCES
    // =====================================================================
    const btnAddMenuTop      = document.getElementById('btnAddMenuTop');
    const btnAddMenuFloating = document.getElementById('btnAddMenu'); 
    const menuModalEl        = document.getElementById('menuModal');
    const menuModal          = new bootstrap.Modal(menuModalEl);
    const form               = document.getElementById('menuForm');
    const treeContainer      = document.getElementById('menuTreeContainer');

    const btnAddCategory     = document.getElementById('btnAddCategory');
    const categoryModalEl    = document.getElementById('categoryModal');
    const categoryModal      = new bootstrap.Modal(categoryModalEl);
    const categoryForm       = document.getElementById('categoryForm');
    const categoryList       = document.getElementById('categoryList');

    if (!menuModalEl || !form || !treeContainer) {
        console.error("Menu modal, form, or tree container missing.");
        return;
    }

    // =====================================================================
    // FETCH MENU TREE (Admin Mode: ALL menus)
    // =====================================================================
    const fetchMenus = () => {
        treeContainer.innerHTML = `
            <div class="text-muted text-center py-4">Loading...</div>
        `;

        fetch('/menu-management/tree')
            .then(res => res.json())
            .then(json => {
                if (!json.success) {
                    treeContainer.innerHTML = `
                        <div class="text-muted text-center py-4">
                            Error: ${json.message}
                        </div>
                    `;
                    return;
                }
                renderTree(json.data);
            })
            .catch(err => {
                console.error(err);
                treeContainer.innerHTML = `
                    <div class="text-muted text-center py-4">
                        Error loading menu structure.
                    </div>
                `;
            });
    };

    // =====================================================================
    // RENDER TREE
    // =====================================================================
    const renderTree = (blocks) => {
        treeContainer.innerHTML = '';

        if (!blocks.length) {
            treeContainer.innerHTML = `
                <div class="text-muted text-center py-4">
                    No menu items found.
                </div>
            `;
            return;
        }

        const root = document.createElement('ul');
        root.classList.add('tree');

        blocks.forEach(block => {
            const li = document.createElement('li');
            li.classList.add('open');

            // Category header row
            const header = document.createElement('div');
            header.className = 'd-flex justify-content-between align-items-center mb-1';

            const catLabel = document.createElement('span');
            catLabel.className = 'fw-bold text-primary';
            catLabel.textContent = block.category?.menu_category ?? 'Uncategorized';

            header.appendChild(catLabel);
            li.appendChild(header);

            if (block.menus?.length) {
                const ul = document.createElement('ul');
                ul.classList.add('open');

                block.menus.forEach(menu => {
                    ul.appendChild(buildMenuNode(menu));
                });

                li.appendChild(ul);
            }

            root.appendChild(li);
        });

        treeContainer.appendChild(root);

        wireToggleHandlers();
    };

    // =====================================================================
    // BUILD A MENU NODE
    // =====================================================================
    const buildMenuNode = (menu) => {
        const li = document.createElement('li');
        li.classList.add('open');

        const label = document.createElement('span');
        label.className = 'tree-label d-inline-flex align-items-center gap-1';

        const hasChildren = Array.isArray(menu.children) && menu.children.length > 0;

        // Chevron or spacer
        if (hasChildren) {
            const toggle = document.createElement('i');
            toggle.className = 'fas fa-chevron-right tree-toggle me-1';
            label.appendChild(toggle);
        } else {
            const spacer = document.createElement('i');
            spacer.className = 'fa fa-window-minimize text-muted small me-1';
            label.appendChild(spacer);
        }

        // Icon
        if (menu.icon) {
            const icon = document.createElement('i');
            icon.className = menu.icon;
            label.appendChild(icon);
        }

        // Title
        label.appendChild(document.createTextNode(menu.title));

        // Route badge
        const badge = document.createElement('span');
        badge.className = 'badge bg-light text-dark ms-1';
        badge.textContent = menu.route || '/';
        label.appendChild(badge);

        li.appendChild(label);

        // Actions
        li.insertAdjacentHTML('beforeend', `
            <span class="tree-actions ms-2">
                <button class="btn btn-sm btn-warning btn-edit" data-id="${menu.id}">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger btn-delete" data-id="${menu.id}">
                    <i class="fas fa-trash"></i>
                </button>
            </span>
        `);

        // Children
        if (hasChildren) {
            const ul = document.createElement('ul');
            ul.classList.add('open');
            menu.children.forEach(child => {
                ul.appendChild(buildMenuNode(child));
            });
            li.appendChild(ul);
        }

        return li;
    };

    // =====================================================================
    // WIRE TOGGLES
    // =====================================================================
    const wireToggleHandlers = () => {
        treeContainer.querySelectorAll('.tree-label').forEach(label => {
            label.addEventListener('click', function (e) {

                const li = this.closest('li');
                const list = li.querySelector(':scope > ul');
                if (!list) return;

                e.stopPropagation();

                const isOpen = li.classList.contains('open');
                li.classList.toggle('open', !isOpen);

                const chevron = this.querySelector('.tree-toggle');
                if (chevron) {
                    chevron.classList.toggle('open', !isOpen);
                }
            });
        });
    };

    // =====================================================================
    // ADD MENU
    // =====================================================================
    const openAddMenu = () => {
        form.reset();
        form.querySelector('#menu_id').value = '';
        document.getElementById('menuModalLabel').textContent = "Add Menu";
        menuModal.show();
    };

    if (btnAddMenuTop) btnAddMenuTop.addEventListener('click', openAddMenu);
    if (btnAddMenuFloating) btnAddMenuFloating.addEventListener('click', openAddMenu);

    // =====================================================================
    // SUBMIT MENU FORM
    // =====================================================================
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const fd = new FormData(form);
        const id = fd.get('id');
        const url = id ? '/menu-management/update' : '/menu-management/create';

        fetch(url, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(json => {
                if (json.success) {
                    Swal.fire("Success", json.message, "success");
                    menuModal.hide();
                    fetchMenus();
                } else {
                    Swal.fire("Error", json.message, "error");
                }
            })
            .catch(err => Swal.fire("Error", err.message, "error"));
    });

    // =====================================================================
    // EDIT / DELETE (Delegation)
    // =====================================================================
    treeContainer.addEventListener('click', (e) => {
        const btnEdit   = e.target.closest('.btn-edit');
        const btnDelete = e.target.closest('.btn-delete');

        // ------- EDIT -------
        if (btnEdit) {
            const id = btnEdit.dataset.id;

            fetch(`/menu-management/get/${id}`)
                .then(res => res.json())
                .then(json => {
                    if (!json.success) {
                        Swal.fire("Error", json.message, "error");
                        return;
                    }
                    const m = json.data;

                    form.querySelector('#menu_id').value       = m.id;
                    form.querySelector('#title').value         = m.title;
                    form.querySelector('#category_id').value   = m.category_id ?? '';
                    form.querySelector('#parent_id').value     = m.parent_id ?? '';
                    form.querySelector('#route').value         = m.route ?? '';
                    form.querySelector('#icon').value          = m.icon ?? '';
                    form.querySelector('#permission').value    = m.permission ?? '';
                    form.querySelector('#position').value      = m.position ?? 0;
                    form.querySelector('#is_active').checked   = m.is_active == 1;

                    document.getElementById('menuModalLabel').textContent = "Edit Menu";
                    menuModal.show();
                });
            return;
        }

        // ------- DELETE -------
        if (btnDelete) {
            const id = btnDelete.dataset.id;

            Swal.fire({
                title: "Delete this menu?",
                text: "This cannot be undone.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Delete"
            }).then(result => {
                if (!result.isConfirmed) return;

                const fd = new FormData();
                fd.append("id", id);

                fetch('/menu-management/delete', {
                    method: 'POST',
                    body: fd
                })
                    .then(r => r.json())
                    .then(json => {
                        Swal.fire(
                            json.success ? "Deleted" : "Error",
                            json.message,
                            json.success ? "success" : "error"
                        );
                        fetchMenus();
                    });
            });

            return;
        }
    });

    // =====================================================================
    // CATEGORY EVENTS (Add/Edit/Delete/Sort)
    // =====================================================================

    // Add category
    if (btnAddCategory) {
        btnAddCategory.addEventListener('click', () => {
            categoryForm.reset();
            categoryForm.querySelector('#category_id').value = '';
            document.getElementById('categoryModalLabel').textContent = "Add Category";
            categoryModal.show();
        });
    }

    // Submit category form
    categoryForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const fd = new FormData(categoryForm);

        const id = fd.get("id");
        const url = id ? '/category/update' : '/category/create';

        fetch(url, { method: 'POST', body: fd })
            .then(res => res.json())
            .then(json => {
                if (json.success) {
                    Swal.fire("Success", json.message, "success");
                    categoryModal.hide();
                    refreshCategories();
                    fetchMenus();
                } else {
                    Swal.fire("Error", json.message, "error");
                }
            });
    });

    // Refresh category panel (left side)
    const refreshCategories = () => {
        fetch('/category/list')
            .then(res => res.json())
            .then(json => {
                if (!json.success) return;

                const list = document.getElementById('categoryList');
                list.innerHTML = '';

                json.data.forEach(cat => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item d-flex justify-content-between align-items-center small';
                    li.dataset.id = cat.id;

                    li.innerHTML = `
                        <span class="cat-label">
                            ${cat.menu_category}
                        </span>

                        <span class="cat-actions">

                            <button class="btn btn-sm btn-warning btn-cat-edit" data-id="${cat.id}">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button class="btn btn-sm btn-danger btn-cat-delete" data-id="${cat.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </span>
                    `;

                    list.appendChild(li);
                });
            });
    };


    // Category action handlers
    categoryList.addEventListener('click', (e) => {
        const li = e.target.closest('li[data-id]');
        if (!li) return;
        const id = li.dataset.id;

        // Edit
        if (e.target.closest('.btn-cat-edit')) {
            fetch(`/category/get/${id}`)
                .then(r => r.json())
                .then(json => {
                    if (!json.success) {
                        Swal.fire("Error", json.message, "error");
                        return;
                    }

                    const cat = json.data;

                    categoryForm.querySelector('#category_id').value = cat.id;
                    categoryForm.querySelector('#category_name').value = cat.menu_category;
                    categoryForm.querySelector('#category_position').value = cat.position ?? 0;

                    document.getElementById('categoryModalLabel').textContent = "Edit Category";
                    categoryModal.show();
                });
            return;
        }

        // Delete
        if (e.target.closest('.btn-cat-delete')) {
            Swal.fire({
                title: "Delete category?",
                icon: "warning",
                showCancelButton: true
            }).then(result => {
                if (!result.isConfirmed) return;

                const fd = new FormData();
                fd.append("id", id);

                fetch('/category/delete', {
                    method: 'POST',
                    body: fd
                })
                    .then(r => r.json())
                    .then(json => {
                        Swal.fire(
                            json.success ? "Deleted" : "Error",
                            json.message,
                            json.success ? "success" : "error"
                        );
                        refreshCategories();
                        fetchMenus();
                    });
            });
            return;
        }

        // Reorder
        if (e.target.closest('.btn-cat-up') || e.target.closest('.btn-cat-down')) {
            const direction = e.target.closest('.btn-cat-up') ? 'up' : 'down';

            const fd = new FormData();
            fd.append("id", id);
            fd.append("direction", direction);

            fetch('/category/reorder', {
                method: 'POST',
                body: fd
            })
                .then(r => r.json())
                .then(() => {
                    refreshCategories();
                    fetchMenus();
                });

            return;
        }
    });

    // =====================================================================
    // INITIAL LOAD
    // =====================================================================
    fetchMenus();
});