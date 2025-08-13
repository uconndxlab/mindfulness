document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        pauseAllAudio();
    }
});

function pauseAllAudio() {
    const audios = document.querySelectorAll('audio');
    audios.forEach((audio) => audio.pause());
}

// Logout handling
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', logoutClick);
}

function logoutClick() {
    if (typeof window.showModal === 'function') {
        window.showModal({
            label: 'Are you sure you want to logout?',
            route: '/logout',
            method: 'POST',
            buttonLabel: 'Logout',
            buttonClass: 'btn-danger',
        });
    }
}

// Handle session modal data (in case modal component loads after)
if (window.sessionModalData && typeof window.showModal === 'function') {
    window.showModal(window.sessionModalData);
}
