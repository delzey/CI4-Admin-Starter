document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#tblMenus tbody');
    const menuModal = new bootstrap.Modal(document.getElementById('menuModal'));
    const form = document.getElementById('menuForm');
    const btnAdd = document.getElementById('btnAddMenu');

    const fetchMenus = () => {
        fetch('/menu-management/view')
            .then(res => res.json())
            .then(json => renderTable(json.data || []))
            .catch(err => console.error(err));
    };

    const renderTable = (menus) => {
        tableBody.innerHTML = '';
        menus.forEach(menu => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${menu.id}</td>
                <td>${menu.title}</td>
                <td>${menu.route || ''}</td>
                <td>${menu.parent_id || ''}</td>
                <td>${menu.position || 0}</td>
                <td>${menu.is_active == 1 ? 'Yes' : 'No'}</td>
                <td>
                    <button class="btn btn-sm btn-secondary me-1 btn-up" data-id="${menu.id}">▲</button>
                    <button class="btn btn-sm btn-secondary me-1 btn-down" data-id="${menu.id}">▼</button>
                    <button class="btn btn-sm btn-warning me-1 btn-edit" data-id="${menu.id}">Edit</button>
                    <button class="btn btn-sm btn-danger btn-delete" data-id="${menu.id}">Delete</button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
    };

    btnAdd.addEventListener('click', () => {
        form.reset();
        form.querySelector('#menu_id').value = '';
        document.getElementById('menuModalLabel').textContent = 'Add Menu';
        menuModal.show();
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const id = formData.get('id');
        const url = id ? '/menu-management/update' : '/menu-management/create';

        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(json => {
            if (json.success) {
                Swal.fire('Success', json.message, 'success');
                menuModal.hide();
                fetchMenus();
            } else {
                Swal.fire('Error', json.message, 'error');
            }
        })
        .catch(err => Swal.fire('Error', err.message, 'error'));
    });

    tableBody.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-edit')) {
            const id = e.target.dataset.id;
            fetch('/menu-management/view')
                .then(res => res.json())
                .then(json => {
                    const item = (json.data || []).find(m => m.id == id);
                    if (!item) return;
                    document.getElementById('menu_id').value = item.id;
                    document.getElementById('title').value = item.title;
                    document.getElementById('category_id').value = item.category_id || '';
                    document.getElementById('parent_id').value = item.parent_id || '';
                    document.getElementById('route').value = item.route || '';
                    document.getElementById('icon').value = item.icon || '';
                    document.getElementById('permission').value = item.permission || '';
                    document.getElementById('position').value = item.position || 0;
                    document.getElementById('is_active').checked = item.is_active == 1;
                    document.getElementById('menuModalLabel').textContent = 'Edit Menu';
                    menuModal.show();
                });
        }

        if (e.target.classList.contains('btn-delete')) {
            const id = e.target.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will delete the menu item permanently.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
            }).then(result => {
                if (!result.isConfirmed) return;
                const formData = new FormData();
                formData.append('id', id);
                fetch('/menu-management/delete', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(json => {
                    Swal.fire(json.success ? 'Deleted' : 'Error', json.message, json.success ? 'success' : 'error');
                    fetchMenus();
                });
            });
        }
        
        if (e.target.classList.contains('btn-up') || e.target.classList.contains('btn-down')) {
            const id = e.target.dataset.id;
            const direction = e.target.classList.contains('btn-up') ? 'up' : 'down';
            const formData = new FormData();
            formData.append('id', id);
            formData.append('direction', direction);
            fetch('/menu-management/reorder', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(json => {
                    if (json.success) {
                        fetchMenus();
                    } else {
                        Swal.fire('Info', json.message, 'info');
                    }
                })
                .catch(err => Swal.fire('Error', err.message, 'error'));
        }
    });

    fetchMenus();
});

document.addEventListener("click", e => {
    if (e.target.closest(".tree-label")) {
        const li = e.target.closest("li");
        li.classList.toggle("open");
    }
});
