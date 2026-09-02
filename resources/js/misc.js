function pauseAllAudio() {
    document.querySelectorAll('audio').forEach(audio => { try { audio.pause(); } catch (_) {} });
}
if (!window.pauseAllAudio) window.pauseAllAudio = pauseAllAudio;

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

const topNav = document.querySelector('.app-top-nav');
if (topNav) {
    const syncTopNavHeight = () => {
        document.documentElement.style.setProperty('--top-nav-height', `${topNav.offsetHeight}px`);
    };
    syncTopNavHeight();
    window.addEventListener('resize', syncTopNavHeight);

    const handleScroll = () => {
        if (window.scrollY > 0) {
            topNav.classList.add('scrolled');
        } else {
            topNav.classList.remove('scrolled');
        }
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    handleScroll();
}
