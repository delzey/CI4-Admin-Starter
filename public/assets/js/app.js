document.addEventListener("DOMContentLoaded", () => {

    const badge = document.getElementById('navbarMsgBadge');
    if (!badge) return;

    const loadUnread = () => {
        fetch('/messages/unread-count')
            .then(r => r.json())
            .then(d => {
                if (!d || typeof d.count === 'undefined') return;

                if (d.count > 0) {
                    badge.textContent = d.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(() => {});
    };

    loadUnread();

    // Refresh every 30 seconds
    setInterval(loadUnread, 30000);
});

document.addEventListener("DOMContentLoaded", () => {

    const badge = document.getElementById('navbarMsgBadge');
    const list = document.getElementById('navbarMsgContainer');

    const loadUnread = () => {
        fetch('/messages/unread-count')
            .then(r => r.json())
            .then(d => {
                if (!d || typeof d.count === 'undefined') return;

                if (d.count > 0) {
                    badge.textContent = d.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(() => {});
    };

    const loadPreview = () => {
        if (!list) return;

        list.innerHTML = `<div class="text-center text-muted py-3">Loading…</div>`;

        fetch('/messages/inbox-preview')
            .then(r => r.json())
            .then(d => {
                if (!d.success || d.data.length === 0) {
                    list.innerHTML = `<div class="text-center text-muted py-3">No messages</div>`;
                    return;
                }

                list.innerHTML = '';

                d.data.forEach(msg => {
                    const li = document.createElement('a');
                    li.href = `/messages/read/${msg.id}`;
                    li.className = `list-group-item list-group-item-action ${msg.is_read ? '' : 'unread'}`;

                    li.innerHTML = `
                        <div class="fw-semibold">${msg.subject}</div>
                        <div class="msg-preview text-muted small">${msg.sender} • ${msg.sent_at}</div>
                    `;

                    list.appendChild(li);
                });
            });
    };

    // Load badge immediately
    loadUnread();
    setInterval(loadUnread, 30000);

    // Load mini inbox only when dropdown is opened
    const dropdownBtn = document.getElementById('navbarMessagesDropdown');
    dropdownBtn?.addEventListener('click', loadPreview);
});
