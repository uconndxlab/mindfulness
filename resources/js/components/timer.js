function initTimer() {
    const timerContainer = document.getElementById('timer-container');
    if (!timerContainer) return;

    const presetTime = parseInt(timerContainer.getAttribute('data-preset-time')) || null;

    let selectedMinutes = presetTime || 5;
    let timeRemaining = selectedMinutes * 60;
    let timerInterval = null;
    let isRunning = false;

    const timerDisplay = document.getElementById('timer-display');
    const playPauseButton = document.getElementById('timer-play-pause');
    const playPauseIcon = playPauseButton ? playPauseButton.querySelector('.bi') : null;
    const resetButton = document.getElementById('timer-reset');
    const timeSelector = document.getElementById('time-selector');

    function formatTime(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    function updateDisplay() {
        if (timerDisplay) {
            timerDisplay.textContent = formatTime(timeRemaining);
        }
    }

    function updatePlayPauseIcon() {
        if (!playPauseIcon) return;
        if (isRunning) {
            playPauseIcon.className = 'bi bi-pause';
            // playPauseButton.classList.add('playing');
        } else {
            playPauseIcon.className = 'bi bi-play';
            // playPauseButton.classList.remove('playing');
        }
    }

    function startTimer() {
        if (isRunning) return;
        isRunning = true;
        updatePlayPauseIcon();
        if (timeSelector) timeSelector.disabled = true;

        timerInterval = setInterval(() => {
            timeRemaining--;
            updateDisplay();

            if (timeRemaining <= 0) {
                clearInterval(timerInterval);
                isRunning = false;
                updatePlayPauseIcon();
                completeActivity();
            }
        }, 1000);
    }

    function pauseTimer() {
        if (!isRunning) return;
        isRunning = false;
        clearInterval(timerInterval);
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
        clearInterval(timerInterval);
        timeRemaining = selectedMinutes * 60;
        updateDisplay();
        updatePlayPauseIcon();
        if (timeSelector) timeSelector.disabled = false;
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

    // init display
    updateDisplay();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTimer);
} else {
    initTimer();
}