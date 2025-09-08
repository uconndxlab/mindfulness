function initAuthLoginRegister() {
    const tz = document.getElementById('timezone');
    if (!tz) return;
    try {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        tz.value = timezone;
    } catch (e) {
        // ignore
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthLoginRegister);
} else {
    initAuthLoginRegister();
}


