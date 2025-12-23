// public/assets/js/pages/profile.js
(function ($) {
  'use strict';

  if (typeof window.site === 'undefined') return;

  const $form = $('#formProfile');
  if ($form.length === 0) return;

  $form.on('submit', function (e) {
    e.preventDefault();

    const formData = $form.serializeArray();
    formData.push({ name: site.csrfName, value: site.csrfHash });

    $.ajax({
      url: site.profileUpdateUrl,
      type: 'POST',
      dataType: 'json',
      data: $.param(formData),
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      success: function (res) {
        if (res && res.success) {
          if (window.Swal) {
            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: res.messages?.general || 'Profile updated',
              showConfirmButton: false,
              timer: 1500
            });
          } else {
            alert(res.messages?.general || 'Profile updated');
          }
        } else {
          // Show validation errors
          const messages = res.messages || {};
          let msg = messages.general || 'Unable to update profile.';
          if (window.Swal) {
            Swal.fire({
              icon: 'error',
              title: 'Oops',
              text: msg
            });
          } else {
            alert(msg);
          }
        }
      },
      error: function () {
        if (window.Swal) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Unable to update profile at this time.'
          });
        } else {
          alert('Unable to update profile at this time.');
        }
      }
    });
  });

})(jQuery);
