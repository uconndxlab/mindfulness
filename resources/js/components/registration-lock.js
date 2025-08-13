function initAdminDashboardLockButton() {
    const button = document.getElementById('lock_button_reg');
    if (!button) return;

    const icon = document.getElementById('lock_icon_reg');
    const text = document.getElementById('lock_text_reg');
    const route = button.getAttribute('data-route');
    if (!route) return;

    button.addEventListener('click', async () => {
        try {
            const response = await fetch(route, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            });
            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.message || 'Server error');
            }
            const data = await response.json();
            const isLocked = data.status;
            button.classList.toggle('btn-danger', !isLocked);
            button.classList.toggle('btn-primary', isLocked);
            icon.classList.toggle('bi-lock', !isLocked);
            icon.classList.toggle('bi-unlock', isLocked);
            text.textContent = isLocked ? 'UNLOCK REGISTRATION' : 'Lock Registration';
        } catch (e) {
            console.error('Error:', e);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboardLockButton);
} else {
    initAdminDashboardLockButton();
}
