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
            .catch(() => { });
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
            .catch(() => { });
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
                        <div class="msg-preview text-muted small">${msg.sender || 'System'} • ${msg.sent_at}</div>
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

// --- Date formatting --------------------------------------------------------
// Supports common PHP tokens: Y, y, m, n, d, j
function formatDateBySetting(value, format = (window.site?.dateFormat || 'm/d/Y'), tz = (window.site?.timezone || undefined)) {
    if (!value) return '';

    // Parse incoming value (works for ISO strings "2025-12-22 10:30:00" / "2025-12-22T10:30:00Z")
    // If your API returns "YYYY-MM-DD HH:MM:SS" (no timezone), treat as local.
    let date;
    if (typeof value === 'string') {
        // normalize "YYYY-MM-DD HH:MM:SS" -> "YYYY-MM-DDTHH:MM:SS"
        const normalized = value.includes(' ') && !value.includes('T')
            ? value.replace(' ', 'T')
            : value;

        date = new Date(normalized);
    } else {
        date = new Date(value);
    }

    if (isNaN(date.getTime())) return String(value);

    // Convert PHP-ish format -> Intl options (simple + practical)
    // We only map the basics; you can extend this as needed.
    const map = {
        Y: () => ({ year: 'numeric' }),
        y: () => ({ year: '2-digit' }),
        m: () => ({ month: '2-digit' }),
        n: () => ({ month: 'numeric' }),
        d: () => ({ day: '2-digit' }),
        j: () => ({ day: 'numeric' }),
    };

    // Build options based on tokens included
    let opts = {};
    for (const ch of format) {
        if (map[ch]) opts = { ...opts, ...map[ch]() };
    }

    // If format includes time tokens in the future, add mappings here (H,i,s,etc).
    const formatter = new Intl.DateTimeFormat(undefined, {
        ...opts,
        ...(tz ? { timeZone: tz } : {}),
    });

    return formatter.format(date);
}