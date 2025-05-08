// Audio handling
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        pauseAllAudio();
    }
});

function pauseAllAudio() {
    const audios = document.querySelectorAll('audio');
    audios.forEach(audio => {
        audio.pause();
    });
}

// Logout handling
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', logoutClick);
}

function logoutClick() {
    showModal({
        label: 'Are you sure you want to logout?',
        route: '/logout',
        method: 'POST',
        buttonLabel: 'Logout',
        buttonClass: 'btn-danger',
    });
}

// Handle session modal data
if (window.sessionModalData) {
    showModal(window.sessionModalData);
} 