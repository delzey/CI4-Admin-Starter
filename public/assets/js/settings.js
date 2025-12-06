document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('settingsForm');
    const btnSave = document.getElementById('btnSave');

    const loadSettings = () => {
        fetch('/settings/get')
            .then(res => res.json())
            .then(json => {
                const data = json.data || {};
                document.getElementById('app_theme').value = data['app.theme'] || 'light';
                document.getElementById('app_sidebar_collapsed').checked = !!data['app.sidebar_collapsed'];
            })
            .catch(err => console.error(err));
    };

    btnSave.addEventListener('click', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        formData.set('app_sidebar_collapsed',
            document.getElementById('app_sidebar_collapsed').checked ? '1' : '0'
        );

        fetch('/settings/save', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
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
        .catch(err => Swal.fire('Error', err.message, 'error'));
    });

    loadSettings();
});

