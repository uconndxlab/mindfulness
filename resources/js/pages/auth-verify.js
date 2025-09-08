function initAuthVerify() {
    const CHECK_MS = 3000;
    let intervalId = null;

    async function checkVerification() {
        try {
            const response = await window.axios.get('/check-verification');
            if (response.data?.verified) {
                window.location.href = '/welcome';
            }
        } catch (e) {
            console.error('Error checking verification:', e);
        }
    }

    function startTimer() {
        if (intervalId) clearInterval(intervalId);
        intervalId = setInterval(checkVerification, CHECK_MS);
    }

    function stopTimer() {
        if (intervalId) clearInterval(intervalId);
        intervalId = null;
    }

    startTimer();
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) stopTimer();
        else startTimer();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAuthVerify);
} else {
    initAuthVerify();
}


