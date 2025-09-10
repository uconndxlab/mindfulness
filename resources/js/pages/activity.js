function initActivityPage() {
    const root = document.querySelector('[data-activity-root]') || document.body;
    const activityId = parseInt(root.getAttribute('data-activity-id') || '0', 10);
    const startLogId = parseInt(root.getAttribute('data-start-log-id') || '0', 10);
    const dayName = root.getAttribute('data-day-name') || '';
    const status = root.getAttribute('data-status') || 'unlocked';
    const hasContent = root.getAttribute('data-has-content') === 'true';
    const hasQuiz = root.getAttribute('data-has-quiz') === 'true';
    const hasJournal = root.getAttribute('data-has-journal') === 'true';
    const favoriteToggleRoute = root.getAttribute('data-favorite-toggle-route') || '/favorite/toggle';
    const logInteractionRoute = root.getAttribute('data-log-interaction-route') || '/activities/log-interaction';
    const skipRoute = root.getAttribute('data-skip-route') || `/activities/${activityId}/skip`;

    const redirectDiv = document.getElementById('redirect_div');
    const compLateBtn = document.getElementById('complete-later');
    let completed = false;
    let type = null;

    function unlockRedirect() {
        if (!redirectDiv) return;
        redirectDiv.querySelectorAll('.redirect-btn').forEach(btn => {
            btn.classList.remove('d-none');
            btn.classList.remove('disabled');
        });
        if (compLateBtn) compLateBtn.classList.add('d-none');
    }

    function showCompletionMessage() {
        const completionMessageDiv = document.getElementById('comp_message');
        if (completionMessageDiv) {
            completionMessageDiv.classList.remove('d-none');
        }
    }

    function activityComplete(message = true) {
        console.log('activity completed');
        completed = true;
        if (status === 'unlocked' || status === 'completed') {
            window.axios.post('/activities/complete', {
                activity_id: activityId,
                start_log_id: startLogId
            }).then(response => {
                const data = response.data;
                if (data?.success) {
                    console.log('ProgressService: ' + data.message);
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        return;
                    }
                    if (type === 'image') {
                        const completeButton = document.getElementById('img_complete_activity');
                        if (completeButton) {
                            completeButton.classList.add('disabled');
                            completeButton.classList.add('d-none');
                        }
                    }
                    if (status === 'unlocked') {
                        unlockRedirect(message);
                    }
                    showCompletionMessage();
                }
            }).catch(error => {
                console.error('There was an error updating the progress:', error);
                alert('Error: ' + (error?.message || 'Unknown error'));
            });
        }
    }
    // expose globally for subcomponents
    window.activityComplete = activityComplete;

    // Favorites handling
    const favButton = document.getElementById('favorite_btn');
    let isFavorited = (root.getAttribute('data-is-favorited') === 'true');
    const favIcon = document.getElementById('favorite_icon');
    if (isFavorited && favIcon) favIcon.className = 'bi bi-star-fill';
    function toggleFavorite() {
        const currentState = isFavorited;
        console.log('Current state: ', currentState);
        isFavorited = !isFavorited;
        if (favIcon) favIcon.className = isFavorited ? 'bi bi-star-fill' : 'bi bi-star';
        return new Promise((resolve, reject) => {
            window.axios.post(favoriteToggleRoute, {
                activity_id: activityId
            }).then(response => {
                console.log(response.data?.message || 'Favorited');
                resolve(true);
            }).catch(error => {
                console.error('There was an error toggling favorite', error);
                isFavorited = currentState;
                if (favIcon) favIcon.className = isFavorited ? 'bi bi-star-fill' : 'bi bi-star';
                reject(false);
            });
        });
    }
    if (favButton) {
        favButton.addEventListener('click', function() {
            console.log('Toggling favorite');
            toggleFavorite();
        });
    }

    // Initialize content completion bindings
    (function bindCompletionHandlers() {
        if (hasContent) {
            console.log('Type: content');
            const contentMain = document.getElementById('content_main');
            type = contentMain ? contentMain.getAttribute('data-type') : null;
            const content = document.getElementById('content_view');
            if (content) {
                if (type === 'pdf') {
                    const downloadButton = document.getElementById('download_btn');
                    if (downloadButton) downloadButton.addEventListener('click', activityComplete);
                    content.addEventListener('click', activityComplete);
                } else if (type === 'image' && status !== 'completed') {
                    const completeButton = document.getElementById('img_complete_activity');
                    if (completeButton) {
                        completeButton.classList.remove('disabled');
                        completeButton.addEventListener('click', activityComplete);
                        completeButton.classList.remove('d-none');
                        if (completeButton.parentElement) {
                            completeButton.parentElement.classList.add('d-flex', 'flex-column', 'align-items-center');
                        }
                    }
                } else if (type === 'video') {
                    const videoPlayer = document.getElementById('content_view');
                    if (videoPlayer) {
                        console.log('video player found');
                        videoPlayer.addEventListener('ended', activityComplete);
                    } else {
                        console.log('video player not found');
                    }
                }
            } else {
                // handle all audio players (both in audio_content and main content area)
                const audioPlayers = document.querySelectorAll('.slide__audio-player');
                audioPlayers.forEach(player => {
                    console.log('Adding completion listener to audio player');
                    player.addEventListener('ended', activityComplete);
                });
            }
        } else if (hasQuiz) {
            console.log('Type: quiz');
        } else if (hasJournal) {
            console.log('Type: journal');
        }
    })();

    // Logging interactions
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    let lastFocusTimestamp = performance.now();
    let exited = false;

    function logInteraction(eventType, duration) {
        if (exited) return;
        console.log('Logging interaction: ', eventType);
        const data = new FormData();
        data.append('activity_id', String(activityId));
        data.append('event_type', eventType);
        data.append('_token', csrfToken);
        if (duration) data.append('duration', String(Math.round(duration / 1000)));
        if (eventType === 'exited') data.append('start_log_id', String(startLogId));

        if (eventType === 'exited') {
            exited = true;
            navigator.sendBeacon(logInteractionRoute, data);
        } else {
            window.axios.post(logInteractionRoute, data)
                .catch(error => console.error(`Error logging ${eventType}:`, error));
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            const duration = performance.now() - lastFocusTimestamp;
            logInteraction('unfocused', duration);
        } else {
            lastFocusTimestamp = performance.now();
            logInteraction('refocused');
        }
    });

    window.addEventListener('pagehide', () => {
        console.log('Page hidden');
        const duration = performance.now() - lastFocusTimestamp;
        logInteraction('exited', duration);
    });

    // Page unload warning for progress
    let showBrowserModal = true;
    window.addEventListener('beforeunload', function(e) {
        if (!completed && showBrowserModal) {
            e.preventDefault();
        }
    });

    const backButton = document.getElementById('backButton');
    if (backButton) {
        backButton.addEventListener('click', function(event) {
            event.preventDefault();
            showBrowserModal = false;
            if (!completed && window.showModal) {
                window.showModal({
                    label: 'Leave activity?',
                    body: 'Leaving will erase your progress on this activity. Are you sure you want to leave?',
                    route: this.href,
                    method: 'GET',
                    buttonLabel: 'Leave Activity',
                    buttonClass: 'btn-danger',
                    closeLabel: 'Stay',
                    onCancel: function() {
                        console.log('cancelled in leave');
                        showBrowserModal = true;
                    }
                });
            } else {
                window.location.href = this.href;
            }
        });
    }

    if (compLateBtn) {
        compLateBtn.addEventListener('click', function(event) {
            event.preventDefault();
            showBrowserModal = false;
            if (window.showModal) {
                window.showModal({
                    label: 'Complete Activity Later?',
                    body: `Click 'Continue' to move on to the next activity. All progress on this activity will be lost. This activity must still be completed later in order to finish ${dayName}.`,
                    route: skipRoute,
                    method: 'POST',
                    buttonLabel: 'Continue',
                    buttonClass: 'btn-danger',
                    onCancel: function() {
                        showBrowserModal = true;
                    }
                });
            }
        });
    }

    // Error display
    const errorDiv = document.getElementById('error-messages');
    window.showError = function showError(errorMessage) {
        if (!errorDiv) return;
        errorDiv.textContent = errorMessage;
        errorDiv.classList.remove('d-none');
    };

    // Secret skip
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.key.toLowerCase() === 'm') {
            event.preventDefault();
            console.log('Secret skip');
            activityComplete();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initActivityPage);
} else {
    initActivityPage();
}


