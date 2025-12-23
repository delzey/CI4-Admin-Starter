document.addEventListener('DOMContentLoaded', () => {
  const Swal = window.Swal;

  function toast(msg, type = 'success') {
    Swal.fire({
      position: 'center',
      icon: type,
      title: msg,
      confirmButtonText: 'OK'
    });
  }

  function withCsrf(fd) {
    if (window.siteData && siteData.csrfName && siteData.csrfHash) {
      fd.set(siteData.csrfName, siteData.csrfHash);
    }
    return fd;
  }

  async function postForm(url, formEl) {
    const fd = withCsrf(new FormData(formEl));

    // normalize checkboxes that might be missing when unchecked
    // (only for fields we know about)
    if (formEl.id === 'formGeneral') {
      if (!fd.has('siteOnline')) fd.set('siteOnline', '0');
    }
    if (formEl.id === 'formCompany') {
      if (!fd.has('allowRegistration')) fd.set('allowRegistration', '0');
      if (!fd.has('emailActivation')) fd.set('emailActivation', '0');
      if (!fd.has('email2FA')) fd.set('email2FA', '0');
      if (!fd.has('allowGoogleLogins')) fd.set('allowGoogleLogins', '0');
    }

    const res = await fetch(url, { method: 'POST', body: fd });
    const json = await res.json();
    if (!res.ok || !json.success) {
      throw new Error(json.message || 'Request failed');
    }
    return json;
  }

  // --- PHP Info Modal -------------------------------------------------------
  const btnPhpInfo = document.getElementById('btnPhpInfo');
  if (btnPhpInfo) {
    btnPhpInfo.addEventListener('click', () => {
      const url = btnPhpInfo.getAttribute('data-href');
      const modalEl = document.getElementById('phpInfoModal');
      const frame = document.getElementById('phpInfoFrame');

      if (!modalEl || !frame || !url) return;

      // set iframe src only when opening (fresh)
      frame.src = url;

      const modal = new bootstrap.Modal(modalEl);
      modal.show();

      // clear iframe when closed (optional)
      modalEl.addEventListener('hidden.bs.modal', () => {
        frame.src = 'about:blank';
      }, { once: true });
    });
  }

  const btnGen = document.getElementById('btnSaveGeneral');
  const btnCo = document.getElementById('btnSaveCompany');

  if (btnGen) {
    btnGen.addEventListener('click', async () => {
      try {
        await postForm('/settings/save-general', document.getElementById('formGeneral'));
        toast('General settings updated.');
      } catch (e) {
        toast(e.message, 'error');
      }
    });
  }

  if (btnCo) {
    btnCo.addEventListener('click', async () => {
      try {
        await postForm('/settings/save-company', document.getElementById('formCompany'));
        toast('Company/Auth settings updated.');
      } catch (e) {
        toast(e.message, 'error');
      }
    });
  }
});
