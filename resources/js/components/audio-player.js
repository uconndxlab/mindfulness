async function initSlideAudioPlayers() {
    const players = document.querySelectorAll('.slide__audio.js-audio');
    if (!players.length) return;

    // Lazy-load NoSleep only when players exist
    let NoSleepCtor = null;
    try {
        // nosleep.js is a tiny lib; add to package.json if missing
        const mod = await import('nosleep.js');
        NoSleepCtor = mod.default;
    } catch (e) {
        console.warn('NoSleep failed to load; continuing without it', e);
    }

    players.forEach(playerEl => {
        const id = playerEl.id?.replace('player-', '') || playerEl.querySelector('audio')?.id?.replace('audio-', '') || 'unknown';
        const audioEl = playerEl.querySelector('audio');
        if (!audioEl) return;

        // derive config flags from DOM data if available
        const allowSeek = playerEl.getAttribute('data-allow-seek') === 'true';
        if (allowSeek) {
            // this class will handle the pointer on the trackbar
            playerEl.classList.add('allow-seek');
        }
        const allowPlaybackRate = playerEl.getAttribute('data-allow-playback-rate') === 'true';

        const noSleep = NoSleepCtor ? new NoSleepCtor() : null;
        let watchedTime = 0;
        let hasBeenPlayed = false;

        const circle = playerEl.querySelector('#seekbar');
        const circlePath = circle ? circle : null;
        const watchedPath = playerEl.querySelector('#watched-progress');
        const svgEl = playerEl.querySelector('svg#circle');

        // Initialize progress arc defaults so stroke-dashoffset works
        let totalLength = 0;
        let ringRadiusFromLength = 0;
        if (circlePath && circlePath.getTotalLength) {
            totalLength = circlePath.getTotalLength();
            circlePath.setAttribute('stroke-dasharray', String(totalLength));
            circlePath.setAttribute('stroke-dashoffset', String(totalLength));
            if (totalLength > 0) {
                ringRadiusFromLength = totalLength / (2 * Math.PI);
            }
        }
        
        // initialize watched progress arc
        if (watchedPath && watchedPath.getTotalLength) {
            const watchedLength = watchedPath.getTotalLength();
            watchedPath.setAttribute('stroke-dasharray', String(watchedLength));
            watchedPath.setAttribute('stroke-dashoffset', String(watchedLength));
        }

        // svg handle
        let handleCircle = null;
        let handleRadius = 4;
        let ringRadius = 47;
        if (svgEl) {
            handleCircle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
            handleCircle.setAttribute('id', 'audio-handle');
            handleCircle.setAttribute('r', String(handleRadius));
            handleCircle.setAttribute('visibility', 'hidden');
            handleCircle.setAttribute('aria-hidden', 'true');
            handleCircle.setAttribute('class', 'audio-handle');
            // Pre-position the handle at the top of the ring to avoid visible jump from (0,0)
            {
                const radTop = (-90 * Math.PI) / 180;
                let initRadius = ringRadius;
                if (!initRadius || initRadius <= 0) {
                    initRadius = ringRadiusFromLength > 0 ? ringRadiusFromLength : 47;
                }
                const cxTop = 50 + initRadius * Math.cos(radTop);
                const cyTop = 50 + initRadius * Math.sin(radTop);
                handleCircle.setAttribute('cx', String(cxTop));
                handleCircle.setAttribute('cy', String(cyTop));
            }
            svgEl.appendChild(handleCircle);

            // estimate ring radius from path bbox if possible, otherwise fall back to length-derived value
            try {
                const bbox = circlePath.getBBox();
                const dim = Math.min(bbox.width || 0, bbox.height || 0);
                if (dim > 0) {
                    ringRadius = dim / 2;
                } else if (ringRadiusFromLength > 0) {
                    ringRadius = ringRadiusFromLength;
                }
            } catch (_) {
                if (ringRadiusFromLength > 0) {
                    ringRadius = ringRadiusFromLength;
                }
            }
        }

        function updatePlayerUI(currentTime, duration) {
            if (!circlePath) return;
            const hasDuration = Number.isFinite(duration) && duration > 0;

            // update current position arc (seekbar)
            if (!hasDuration) {
                if (totalLength) circlePath.setAttribute('stroke-dashoffset', String(totalLength));
            } else {
                const percent = Math.max(0, Math.min(100, (currentTime / duration) * 100));
                const dash = totalLength ? (totalLength - (percent / 100) * totalLength) : 0;
                if (totalLength) circlePath.setAttribute('stroke-dashoffset', String(dash));

                // position of handle
                if (handleCircle) {
                    const canShowHandle = hasBeenPlayed;
                    if (canShowHandle) {
                        // compute angle from top clockwise
                        const angle = (percent / 100) * 360 - 90;
                        const rad = (angle * Math.PI) / 180;
                        // If ringRadius wasn't measurable at init (e.g., hidden), derive from path length now
                        if ((!ringRadius || ringRadius <= 0) && ringRadiusFromLength > 0) {
                            ringRadius = ringRadiusFromLength;
                        }
                        const cx = 50 + ringRadius * Math.cos(rad);
                        const cy = 50 + ringRadius * Math.sin(rad);
                        handleCircle.setAttribute('cx', String(cx));
                        handleCircle.setAttribute('cy', String(cy));
                    }
                }
            }

            // update watched progress arc
            if (watchedPath && hasDuration) {
                const watchedPercent = Math.max(0, Math.min(100, (watchedTime / duration) * 100));
                const watchedLength = watchedPath.getTotalLength ? watchedPath.getTotalLength() : totalLength;
                const watchedDash = watchedLength ? (watchedLength - (watchedPercent / 100) * watchedLength) : 0;
                watchedPath.setAttribute('stroke-dashoffset', String(watchedDash));
            } else if (watchedPath) {
                const watchedLength = watchedPath.getTotalLength ? watchedPath.getTotalLength() : totalLength;
                if (watchedLength) watchedPath.setAttribute('stroke-dashoffset', String(watchedLength));
            }
        }

        // playback rate UI (if enabled)
        if (allowPlaybackRate) {
            const playbackRateValue = playerEl.querySelector(`#speed-value-${id}`);
            const playbackRateRange = playerEl.querySelector(`#audioRange-${id}`);
            if (playbackRateRange && playbackRateValue && audioEl) {
                playbackRateRange.addEventListener('input', function() {
                    playbackRateValue.textContent = playbackRateRange.value;
                    audioEl.playbackRate = parseFloat(playbackRateRange.value);
                });
            }
        }

        // Add pointer-based seeking on the circular control without using inline styles
        (function bindPointerSeek() {
            const controls = playerEl.querySelector('.audio__controls');
            if (!controls) return;

            let dragging = false;

            function percentFromClientPoint(clientX, clientY) {
                const rect = controls.getBoundingClientRect();
                // Convert to SVG viewBox coordinates (0-100)
                const x = ((clientX - rect.left) / rect.width) * 100;
                const y = ((clientY - rect.top) / rect.height) * 100;
                // Angle from center, measured from +X axis; convert so 0 is top and grows clockwise
                let angle = Math.atan2(y - 50, x - 50) * (180 / Math.PI) + 90;
                if (angle < 0) angle += 360;
                return Math.max(0, Math.min(100, angle / 360 * 100));
            }

            function seekFromEvent(e) {
                const duration = audioEl.duration || 0;
                if (!Number.isFinite(duration) || duration <= 0) return;
                const desiredPercent = percentFromClientPoint(e.clientX, e.clientY);
                let targetTime = (desiredPercent * duration) / 100;
                
                if (!allowSeek && typeof watchedTime !== 'undefined' && targetTime > watchedTime) {
                    // stop user time change if they are within 0.15 seconds of the watched time limit
                    if (Math.abs(audioEl.currentTime - watchedTime) < 0.25) {
                        return;
                    }
                    targetTime = watchedTime;
                }
                audioEl.currentTime = Math.max(0, Math.min(duration, targetTime));
                updatePlayerUI(audioEl.currentTime, duration);
            }

            controls.addEventListener('pointerdown', (e) => {
                if (e.target && e.target.closest && e.target.closest('.play-pause')) return;
                if (!hasBeenPlayed) return; // keep same UX: allow seeking after first play
                dragging = true;
                try { controls.setPointerCapture(e.pointerId); } catch (_) {}
                seekFromEvent(e);
            });

            controls.addEventListener('pointermove', (e) => {
                if (!dragging) return;
                seekFromEvent(e);
            });

            const endDrag = (e) => {
                if (!dragging) return;
                dragging = false;
                try { controls.releasePointerCapture(e.pointerId); } catch (_) {}
            };
            window.addEventListener('pointerup', endDrag);
        })();

        function playAudio() {
            if (!hasBeenPlayed) {
                hasBeenPlayed = true;
                // state for css styling
                playerEl.classList.add('played');
            }
            // pause all other audio tags on page
            document.querySelectorAll('audio').forEach(el => {
                if (el !== audioEl && !el.paused) {
                    try { el.pause(); } catch (_) {}
                }
            });
            if (noSleep && noSleep.enable) noSleep.enable();
            audioEl.play().then(() => {
                playerEl.classList.remove('paused');
                playerEl.classList.add('playing');
                const icon = playerEl.querySelector('#icon');
                if (icon) { icon.classList.remove('bi-play'); icon.classList.add('bi-pause'); }
                updatePlayerUI(audioEl.currentTime, audioEl.duration);
            }).catch(error => {
                console.error(`[Player ${id}] Audio play() failed:`, error);
                pauseAudio();
            });
        }

        function pauseAudio() {
            if (audioEl.paused) return;
            if (noSleep && noSleep.disable) noSleep.disable();
            try { audioEl.pause(); } catch (_) {}
            playerEl.classList.remove('playing');
            playerEl.classList.add('paused');
            const icon = playerEl.querySelector('#icon');
            if (icon) { icon.classList.remove('bi-pause'); icon.classList.add('bi-play'); }
            updatePlayerUI(audioEl.currentTime, audioEl.duration);
        }

        const playBtn = playerEl.querySelector('.play-pause');
        if (playBtn) {
            playBtn.addEventListener('click', () => {
                if (audioEl.paused) playAudio(); else pauseAudio();
            });
        }

        const originalPause = audioEl.pause.bind(audioEl);
        audioEl.pause = function() {
            if (audioEl.paused) return;
            if (noSleep && noSleep.disable) noSleep.disable();
            originalPause();
            playerEl.classList.remove('playing');
            playerEl.classList.add('paused');
            const icon = playerEl.querySelector('#icon');
            if (icon) { icon.classList.remove('bi-pause'); icon.classList.add('bi-play'); }
            updatePlayerUI(audioEl.currentTime, audioEl.duration);
        };

        audioEl.addEventListener('timeupdate', () => {
            const currentTime = audioEl.currentTime;
            const duration = audioEl.duration;
            updatePlayerUI(currentTime, duration);
            
            // updating watched time for seeking enforcement
            if (!audioEl.seeking && currentTime > watchedTime) {
                watchedTime = currentTime;
            }
        });

        audioEl.addEventListener('ended', () => {
            if (noSleep && noSleep.disable) noSleep.disable();
            playerEl.classList.remove('playing');
            const icon = playerEl.querySelector('#icon');
            if (icon) { icon.classList.remove('bi-pause'); icon.classList.add('bi-play'); }
            
            // reset ui
            updatePlayerUI(0, 0);
        });

        audioEl.addEventListener('seeking', () => {
            // only limit when allowSeek is false
            if (allowSeek) return;
            
            // limit user if they try to seek beyond their watched progress
            if (audioEl.currentTime > watchedTime) {
                const wasPlaying = !audioEl.paused;
                audioEl.pause();
                audioEl.currentTime = watchedTime;
                if (wasPlaying) {
                    audioEl.play().catch(() => {});
                }
            }
        });
        
        audioEl.addEventListener('seeked', () => updatePlayerUI(audioEl.currentTime, audioEl.duration));
        
        audioEl.addEventListener('loadedmetadata', () => {
            // if seeking allowed, user can seek anywhere
            if (allowSeek && audioEl.duration) {
                watchedTime = audioEl.duration;
            }
            updatePlayerUI(audioEl.currentTime, audioEl.duration);
        });

        updatePlayerUI(0, 0);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSlideAudioPlayers);
} else {
    initSlideAudioPlayers();
}


