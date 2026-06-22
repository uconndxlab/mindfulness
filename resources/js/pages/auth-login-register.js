function initAuthLoginRegister() {
    const tz = document.getElementById('timezone');
    if (tz) {
        try {
            const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            tz.value = timezone;
        } catch (e) {
            // ignore
        }
    }

    document.querySelectorAll('.personal-device-info[data-bs-toggle="popover"]').forEach((el) => {
        new window.bootstrap.Popover(el);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthLoginRegister);
} else {
    initAuthLoginRegister();
}


