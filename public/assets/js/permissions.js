document.addEventListener('DOMContentLoaded', () => {
  const modal = new bootstrap.Modal(document.getElementById('permModal'));
  const modalBody = document.getElementById('permModalBody');

  document.querySelectorAll('.btnViewPerms').forEach(btn => {
    btn.addEventListener('click', e => {
      const userId = btn.dataset.user;
      modalBody.innerHTML = '<div class="text-center text-muted p-4">Loading...</div>';
      modal.show();

      fetch(`/auth-permissions/table/${userId}`)
        .then(res => res.text())
        .then(html => {
          modalBody.innerHTML = html;
          bindToggles();
        })
        .catch(() => modalBody.innerHTML = '<div class="alert alert-danger">Failed to load permissions.</div>');
    });
  });

  function bindToggles() {
    document.querySelectorAll('.tglPerm').forEach(cb => {
      cb.addEventListener('change', e => {
        const data = new URLSearchParams({
          user_id: cb.dataset.user,
          module: cb.dataset.module,
          operation: cb.dataset.operation,
          active: cb.checked ? '1' : '0'
        });

        fetch('/auth-permissions/chgPerm', {
          method: 'POST',
          headers: {'X-Requested-With': 'XMLHttpRequest'},
          body: data
        })
        .then(r => r.json())
        .then(json => {
          Swal.fire({
            toast: true,
            position: 'top-end',
            icon: json.success ? 'success' : 'error',
            title: json.message,
            showConfirmButton: false,
            timer: 1500
          });
        })
        .catch(() => Swal.fire('Error', 'Failed to update permission', 'error'));
      });
    });
  }
});
