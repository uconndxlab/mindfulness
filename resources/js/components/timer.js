function initTimer() {
    const timerContainer = document.getElementById('timer-container');
    if (!timerContainer) return;

    const presetTime = parseInt(timerContainer.getAttribute('data-preset-time')) || null;
    const completeOnFinish = timerContainer.getAttribute('data-complete-on-finish') === 'true';

    const selectedMinutes = presetTime || 5;
    const presetMs = selectedMinutes * 60 * 1000;
    const TICK_MS = 250;

    let remainingMs = presetMs;
    let endsAt = null;
    let timerInterval = null;
    let isRunning = false;
    let hasFinished = false;

    const timerDisplay = document.getElementById('timer-display');
    const playPauseButton = document.getElementById('timer-play-pause');
    const playPauseIcon = playPauseButton ? playPauseButton.querySelector('.bi') : null;
    const resetButton = document.getElementById('timer-reset');
    const timeSelector = document.getElementById('time-selector');

    function formatTime(ms) {
        const totalSeconds = Math.max(0, Math.ceil(ms / 1000));
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    function getRemainingMs() {
        if (isRunning && endsAt != null) {
            return Math.max(0, endsAt - Date.now());
        }
        return remainingMs;
    }

    function updateDisplay() {
        if (timerDisplay) {
            timerDisplay.textContent = formatTime(getRemainingMs());
        }
    }

    function updatePlayPauseIcon() {
        if (!playPauseIcon) return;
        playPauseIcon.className = isRunning ? 'bi bi-pause' : 'bi bi-play';
    }

    function showResetButton() {
        if (resetButton) {
            resetButton.classList.remove('d-none');
        }
    }

    function hideResetButton() {
        if (resetButton) {
            resetButton.classList.add('d-none');
        }
    }

    function stopTicker() {
        clearInterval(timerInterval);
        timerInterval = null;
    }

    function startTicker() {
        stopTicker();
        timerInterval = setInterval(syncFromDeadline, TICK_MS);
    }

    function finishTimer() {
        if (hasFinished) return;
        hasFinished = true;
        isRunning = false;
        endsAt = null;
        remainingMs = 0;
        stopTicker();
        updateDisplay();
        updatePlayPauseIcon();
        showResetButton();

        if (completeOnFinish) {
            completeActivity();
        }
    }

    function syncFromDeadline() {
        remainingMs = getRemainingMs();
        updateDisplay();
        if (isRunning && remainingMs <= 0) {
            finishTimer();
        }
    }

    function startTimer() {
        if (isRunning || remainingMs <= 0) return;
        isRunning = true;
        endsAt = Date.now() + remainingMs;
        hideResetButton();
        updatePlayPauseIcon();
        if (timeSelector) timeSelector.disabled = true;
        startTicker();
        syncFromDeadline();
    }

    function pauseTimer() {
        if (!isRunning) return;
        remainingMs = getRemainingMs();
        endsAt = null;
        isRunning = false;
        stopTicker();
        updateDisplay();
        updatePlayPauseIcon();
    }

    function togglePlayPause() {
        if (isRunning) {
            pauseTimer();
        } else {
            startTimer();
        }
    }

    function resetTimer() {
        isRunning = false;
        hasFinished = false;
        endsAt = null;
        remainingMs = presetMs;
        stopTicker();
        updateDisplay();
        updatePlayPauseIcon();
        hideResetButton();
        if (timeSelector) timeSelector.disabled = false;
    }

    function handleWake() {
        if (!isRunning) return;
        syncFromDeadline();
        if (isRunning) {
            startTicker();
        }
    }

    function completeActivity() {
        const event = new CustomEvent('activity:complete', {
            detail: { message: true, voice: null }
        });
        document.dispatchEvent(event);
    }

    if (playPauseButton) {
        playPauseButton.addEventListener('click', togglePlayPause);
    }

    if (resetButton) {
        resetButton.addEventListener('click', resetTimer);
    }

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            handleWake();
        }
    });
    window.addEventListener('pageshow', handleWake);
    document.addEventListener('resume', handleWake);

    updateDisplay();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTimer);
} else {
    initTimer();
}
