import { escapeHtml } from '../utils/escapeHtml.js';

function initActivityPage() {
    const root = document.querySelector('[data-activity-root]') || document.body;
    const activityId = parseInt(root.getAttribute('data-activity-id') || '0', 10);
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
    let completed = status === 'completed';
    let type = null;
    const hasAudioVideo = type === 'audio' || type === 'video';

    function unlockRedirect() {
        if (!redirectDiv) return;

        // scroll to redirect div
        setTimeout(function() {
            var offset = 125;
            var elementPosition = redirectDiv.getBoundingClientRect().top;
            var offsetPosition = elementPosition + window.pageYOffset - offset;
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }, 200);
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

    function activityComplete(message = true, voice=null) {
        console.log('activity completed');
        completed = true;
        if (voice) {
            engagementMetrics.voice = voice;
        }
        
        // completion time for metrics
        if (!engagementMetrics.completionTime && !engagementMetrics.completed) {
            engagementMetrics.completed = true;
            engagementMetrics.completionTime = performance.now();
            // console.log('Activity completion tracked for engagement metrics');

            if (!userUnfocused) {
                // if user is focused, should not set refocus time
                engagementMetrics.timeToRefocus = 0;
                // console.log('Time to refocus tracked for engagement metrics');
            }
        }
        
        if (status === 'unlocked' || status === 'completed') {
            window.axios.post('/activities/complete', {
                activity_id: activityId,
                start_log_id: engagementMetrics.startLogId
            }).then(response => {
                const data = response.data;
                if (data?.success) {
                    // console.log('ProgressService: ' + data.message);
                    if (data.redirect_url) {
                        // log exit before nav
                        const duration = performance.now() - lastFocusTimestamp;
                        logInteraction('exited', duration);
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
                    unlockRedirect(message);
                    showCompletionMessage();
                }
            }).catch(error => {
                console.error('There was an error updating the progress:', error);
                alert('Error: ' + (error?.message || 'Unknown error'));
            });
        }
    }
    document.addEventListener('activity:complete', function(event) {
        activityComplete(event.detail.message, event.detail.voice);
    });

    // Favorites handling
    const favButton = document.getElementById('favorite_btn');
    let isFavorited = (root.getAttribute('data-is-favorited') === 'true');
    const startFavorited = isFavorited;
    const favIcon = document.getElementById('favorite_icon');
    if (isFavorited && favIcon) favIcon.className = 'bi bi-star-fill';
    function toggleFavorite() {
        const currentState = isFavorited;
        isFavorited = !isFavorited;
        if (favIcon) favIcon.className = isFavorited ? 'bi bi-star-fill' : 'bi bi-star';
        return new Promise((resolve, reject) => {
            window.axios.post(favoriteToggleRoute, {
                activity_id: activityId
            }).then(response => {
                resolve(true);
            }).catch(error => {
                // console.error('There was an error toggling favorite', error);
                isFavorited = currentState;
                if (favIcon) favIcon.className = isFavorited ? 'bi bi-star-fill' : 'bi bi-star';
                reject(false);
            });
        });
    }
    if (favButton) {
        favButton.addEventListener('click', function() {
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
                        videoPlayer.addEventListener('ended', activityComplete);
                        
                        // track pause events
                        videoPlayer.addEventListener('pause', function() {
                            if (!videoPlayer.ended) {
                                engagementMetrics.pauseCount++;
                            }
                        });
                        
                        // track seek events with debouncing and direction
                        let lastVideoTime = 0;
                        const VIDEO_SEEK_THRESHOLD = 1.5; // seek distance should be greater than this
                        const VIDEO_SEEK_DEBOUNCE = 2000; // wait between seeks - prevent spam while dragging
                        let lastVideoSeekDirection = null;
                        
                        videoPlayer.addEventListener('timeupdate', function() {
                            const currentTime = videoPlayer.currentTime;
                            const timeDiff = currentTime - lastVideoTime;
                            const absTimeDiff = Math.abs(timeDiff);
                            const now = performance.now();
                            
                            if (absTimeDiff > VIDEO_SEEK_THRESHOLD && lastVideoTime > 0) {
                                const timeSinceLastSeek = now - engagementMetrics.lastSeekTime;
                                
                                if (timeSinceLastSeek > VIDEO_SEEK_DEBOUNCE || lastVideoSeekDirection != null) {
                                    if (timeDiff > 0 && lastVideoSeekDirection != 'forward') {
                                        // forward
                                        lastVideoSeekDirection = 'forward';
                                        engagementMetrics.seekForwardCount++;
                                    } else if (timeDiff < 0 && lastVideoSeekDirection != 'backward') {
                                        // backward
                                        lastVideoSeekDirection = 'backward';
                                        engagementMetrics.seekBackwardCount++;
                                    }
                                    engagementMetrics.lastSeekTime = now;
                                }
                            }
                            lastVideoTime = currentTime;
                        });
                    } else {
                        console.error('video player not found');
                    }
                }
            } else {
                // handle all audio players (both in audio_content and main content area)
                const audioPlayers = document.querySelectorAll('.slide__audio-player');
                audioPlayers.forEach(player => {
                    player.addEventListener('ended', () => activityComplete(true, player.dataset.voiceDisplay ??  'none'));
                    
                    // track pause events
                    player.addEventListener('pause', function() {
                        if (!player.ended) {
                            engagementMetrics.pauseCount++;
                        }
                    });
                    
                    // track seek events with debouncing and direction
                    let lastAudioTime = 0;
                    const SEEK_THRESHOLD = 1.5; // seconds - must jump more than this
                    const SEEK_DEBOUNCE = 2000; // ms - must wait this long between counting seeks
                    let lastSeekDirection = null;

                    player.addEventListener('timeupdate', function() {
                        const currentTime = player.currentTime;
                        const timeDiff = currentTime - lastAudioTime;
                        const absTimeDiff = Math.abs(timeDiff);
                        const now = performance.now();
                        
                        if (absTimeDiff > SEEK_THRESHOLD && lastAudioTime > 0) {
                            const timeSinceLastSeek = now - engagementMetrics.lastSeekTime;
                            
                            if (timeSinceLastSeek > SEEK_DEBOUNCE || lastSeekDirection != null) {
                                if (timeDiff > 0 && lastSeekDirection != 'forward') {
                                    // forward
                                    lastSeekDirection = 'forward';
                                    engagementMetrics.seekForwardCount++;
                                } else if (timeDiff < 0 && lastSeekDirection != 'backward') {
                                    // backward
                                    lastSeekDirection = 'backward';
                                    engagementMetrics.seekBackwardCount++;
                                }
                                engagementMetrics.lastSeekTime = now;
                            }
                        }
                        lastAudioTime = currentTime;
                    });
                });
            }
        } else if (hasQuiz) {
            console.log('Type: quiz');
        } else if (hasJournal) {
            console.log('Type: journal');
        }
    })();

    // ENGAGEMENT METRICS
    // Logging interactions
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : '';
    let lastFocusTimestamp = performance.now();
    let lastUnfocusTimestamp = 0;
    let exited = false;

    // Engagement metrics tracking
    const engagementMetrics = {
        // general
        startLogId: null,
        visibleTime: 0,
        hiddenTime: 0,
        interactionCount: 0,
        unfocusEvents: [], // {timestamp, duration, type}
        // audio/video
        pauseCount: 0,
        seekForwardCount: 0,
        seekBackwardCount: 0,
        // post-completion - metrics
        // if user is unfocused during completion
        activitySkipped: false,
        completed: false,
        alreadyCompleted: completed,
        completionTime: null, // when activity was completed - will calc time to complete
        timeToRefocus: null, // time from completion to next refocus
        timeToExit: null, // time from completion to exit
        voice: null,
        // favorited
        startFavorited: startFavorited,
        endFavorited: null,
        // for calculating engagement metrics
        sessionStart: performance.now(),
        lastInteractionTime: performance.now(),
        lastSeekTime: 0, // for seek debouncing
    };

    let userUnfocused = false;
    function logInteraction(eventType, duration, additionalData = {}) {
        if (exited) return;
        const data = new FormData();
        data.append('activity_id', String(activityId));
        // rename exited to summary
        data.append('event_type', eventType == 'exited' ? 'summary' : eventType);
        data.append('_token', csrfToken);
        if (eventType === 'exited') data.append('start_log_id', String(engagementMetrics.startLogId));

        // track users focus
        if (eventType === 'unfocused' || eventType === 'frozen' || eventType === 'exited') {
            userUnfocused = true;
        }
        if (eventType === 'refocused' || eventType === 'resumed') {
            userUnfocused = false;
        }

        // track visible time when unfocusing
        if (eventType === 'unfocused' || eventType === 'frozen' || eventType === 'exited') {
            const visibleDuration = duration ? Math.round(duration / 1000) : 0;
            engagementMetrics.visibleTime += visibleDuration;
            // data.append('time_since_interaction', String(Math.round((performance.now() - engagementMetrics.lastInteractionTime) / 1000)));
        }

        // track hidden time when refocusing
        if (eventType === 'refocused' || eventType === 'resumed') {
            const hiddenDuration = lastUnfocusTimestamp > 0 
                ? Math.round((performance.now() - lastUnfocusTimestamp) / 1000) 
                : 0;
            
            if (hiddenDuration > 0) {
                // classify unfocus duration
                let unfocusType = 'short';
                if (hiddenDuration >= 90) unfocusType = 'long';      // > 1.5min
                else if (hiddenDuration >= 10) unfocusType = 'medium'; // 10s - 1.5min
                
                engagementMetrics.unfocusEvents.push({
                    timestamp: Date.now(),
                    duration: hiddenDuration,
                    type: unfocusType
                });
                engagementMetrics.hiddenTime += hiddenDuration;
                
                // data.append('unfocus_type', unfocusType);
                // data.append('hidden_duration', String(hiddenDuration));
            }
        }

        // calculate time to refocus after completion - should only set if user is unfocused
        if (eventType === 'refocused' && engagementMetrics.completionTime && engagementMetrics.timeToRefocus === null) {
            engagementMetrics.timeToRefocus = Math.round((performance.now() - engagementMetrics.completionTime) / 1000);
            // data.append('time_to_refocus_after_completion', String(engagementMetrics.timeToRefocus));
        }

        // add engagement metrics for exited event
        if (eventType === 'exited') {
            // get favorite status
            engagementMetrics.endFavorited = isFavorited;
            // calculate time to exit after completion
            if (engagementMetrics.completionTime) {
                engagementMetrics.timeToExit = Math.round((performance.now() - engagementMetrics.completionTime) / 1000);
            }

            // build metrics
            const metrics = {
                // session metrics
                session_duration: Math.round((performance.now() - engagementMetrics.sessionStart) / 1000),
                // total_visible_time: engagementMetrics.visibleTime,
                total_hidden_time: engagementMetrics.hiddenTime,
                // interaction_count: engagementMetrics.interactionCount,
                
                // unfocus metrics
                total_unfocus_count: engagementMetrics.unfocusEvents.length,
                // short_unfocus_count: engagementMetrics.unfocusEvents.filter(e => e.type === 'short').length,
                // medium_unfocus_count: engagementMetrics.unfocusEvents.filter(e => e.type === 'medium').length,
                // long_unfocus_count: engagementMetrics.unfocusEvents.filter(e => e.type === 'long').length,
                
                // audio/video metrics
                // pause_count: engagementMetrics.pauseCount,
                // seek_forward_count: engagementMetrics.seekForwardCount,
                // seek_backward_count: engagementMetrics.seekBackwardCount,
                
                // completion metrics
                activity_skipped: engagementMetrics.activitySkipped,
                activity_completed: engagementMetrics.completed,
                already_completed: engagementMetrics.alreadyCompleted,
                // other completion metrics done conditionally
                
                // favorites
                start_favorited: engagementMetrics.startFavorited,
                end_favorited: engagementMetrics.endFavorited,
            };
            
            // conditional items
            // add completion-related metrics only if activity was completed
            if (engagementMetrics.completed) {
                metrics.time_to_complete = Math.round((engagementMetrics.completionTime - engagementMetrics.sessionStart) / 1000);
                metrics.time_to_refocus_after_completion = engagementMetrics.timeToRefocus;
                metrics.time_to_exit_after_completion = engagementMetrics.timeToExit;
            }
            if (engagementMetrics.voice) {
                metrics.voice = engagementMetrics.voice;
            }
            if (hasAudioVideo) {
                metrics.pause_count = engagementMetrics.pauseCount;
                metrics.seek_forward_count = engagementMetrics.seekForwardCount;
                metrics.seek_backward_count = engagementMetrics.seekBackwardCount;
            }
            
            // metrics as json
            data.append('metrics', JSON.stringify(metrics));
        }

        // add any additional data
        Object.keys(additionalData).forEach(key => {
            data.append(key, String(additionalData[key]));
        });

        if (eventType === 'exited') {
            exited = true;
            navigator.sendBeacon(logInteractionRoute, data);
        }
        else if (eventType === 'started') {
            // send the request, get the new start log id
            window.axios.post(logInteractionRoute, data).then(response => {
                const responseData = response.data;
                if (responseData?.status === 'success' && responseData?.log_id) {
                    engagementMetrics.startLogId = responseData.log_id;
                }
            }).catch(error => {
                console.error('There was an error logging the interaction:', error);
            });
        }
    }

    // general interactions
    ['click', 'scroll', 'touchstart'].forEach(eventType => {
        document.addEventListener(eventType, () => {
            engagementMetrics.interactionCount++;
            engagementMetrics.lastInteractionTime = performance.now();
        }, { passive: true });
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            const duration = performance.now() - lastFocusTimestamp;
            lastUnfocusTimestamp = performance.now();
            logInteraction('unfocused', duration);
        } else {
            lastFocusTimestamp = performance.now();
            logInteraction('refocused');
        }
    });

    // page lifecycle api - better mobile detection
    if ('onfreeze' in document) {
        document.addEventListener('freeze', () => {
            const duration = performance.now() - lastFocusTimestamp;
            lastUnfocusTimestamp = performance.now();
            logInteraction('frozen', duration, { lifecycle_event: 'freeze' });
        });

        document.addEventListener('resume', () => {
            lastFocusTimestamp = performance.now();
            logInteraction('resumed', 0, { lifecycle_event: 'resume' });
        });
    }

    window.addEventListener('pagehide', () => {
        const duration = performance.now() - lastFocusTimestamp;
        logInteraction('exited', duration);
    });

    // log activity start - runs on script init
    // handles all navigation types (initial, reload, back/forward)
    (function logActivityStart() {
        const navEntries = performance.getEntriesByType('navigation');
        const navType = navEntries.length > 0 ? navEntries[0].type : 'unknown';
        
        console.log('Activity page initialized - Navigation type:', navType);
        
        // For back/forward navigation, reset engagement metrics for new session
        if (navType === 'back_forward') {
            exited = false;
            lastFocusTimestamp = performance.now();
            lastUnfocusTimestamp = 0;
            engagementMetrics.visibleTime = 0;
            engagementMetrics.hiddenTime = 0;
            engagementMetrics.interactionCount = 0;
            engagementMetrics.unfocusEvents = [];
            engagementMetrics.pauseCount = 0;
            engagementMetrics.seekForwardCount = 0;
            engagementMetrics.seekBackwardCount = 0;
            engagementMetrics.activitySkipped = false;
            engagementMetrics.completed = false;
            engagementMetrics.completionTime = null;
            engagementMetrics.timeToRefocus = null;
            engagementMetrics.timeToExit = null;
            engagementMetrics.startFavorited = startFavorited;
            engagementMetrics.endFavorited = null;
            engagementMetrics.sessionStart = performance.now();
            engagementMetrics.lastInteractionTime = performance.now();
            engagementMetrics.lastSeekTime = 0;
        }
        
        // always log started event - bf events, ctrl+shift+t, reload, initial navigation
        logInteraction('started', 0, { navigation_type: navType });
    })();

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
            const href = this.href;
            if (!completed && window.showModal) {
                window.showModal({
                    label: 'Leave activity?',
                    body: 'Leaving will erase your progress on this activity. Are you sure you want to leave?',
                    route: href,
                    method: 'GET',
                    buttonLabel: 'Leave Activity',
                    buttonClass: 'btn-danger',
                    closeLabel: 'Stay',
                    onConfirm: function() {
                        // log exit before nav
                        const duration = performance.now() - lastFocusTimestamp;
                        logInteraction('exited', duration);
                    },
                    onCancel: function() {
                        showBrowserModal = true;
                    }
                });
            } else {
                // manually log exit before nav - without modal
                const duration = performance.now() - lastFocusTimestamp;
                logInteraction('exited', duration);
                window.location.href = href;
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
                    body: `Click 'Continue' to move on to the next activity. All progress on this activity will be lost. This activity must still be completed later in order to finish ${escapeHtml(dayName)}.`,
                    route: skipRoute,
                    method: 'POST',
                    buttonLabel: 'Continue',
                    buttonClass: 'btn-danger',
                    onConfirm: function() {
                        // log exit before nav
                        const duration = performance.now() - lastFocusTimestamp;
                        engagementMetrics.activitySkipped = true;
                        logInteraction('exited', duration);
                    },
                    onCancel: function() {
                        showBrowserModal = true;
                    }
                });
            } else {
                // log exit before nav (no modal)
                const duration = performance.now() - lastFocusTimestamp;
                engagementMetrics.activitySkipped = true;
                logInteraction('exited', duration);
                window.location.href = skipRoute;
            }
        });
    }

    // handle redirect buttons manually to ensure logInteraction('exited') is called
    const redirectButton = document.getElementById('redirect_button');
    if (redirectButton) {
        redirectButton.addEventListener('click', function(event) {
            event.preventDefault();
            // log exit before nav
            const duration = performance.now() - lastFocusTimestamp;
            logInteraction('exited', duration);
            window.location.href = this.href;
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


