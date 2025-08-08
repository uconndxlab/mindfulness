<div id="player-{{ $id }}" class="slide__audio js-audio col-12">
    <audio id="audio-{{ $id }}" class="slide__audio-player media-player" preload="auto" src="{{ Storage::url('content/'.$file) }}"></audio>
    <div class="audio__controls">
        <svg version="1.1" id="circle" width="306px" height="306px" viewBox="0 0 100 100">
            <path id="seekbar" fill="none" stroke-meterlimit="10" d="M50,2.9L50,2.9C76,2.9,97.1,24,97.1,50v0C97.1,76,76,97.1,50,97.1h0C24,97.1,2.9,76,2.9,50v0C2.9,24,24,2.9,50,2.9z"/>
        </svg>
        <div class="audio__slider"></div>
        <button class="play-pause">
            <i id="icon" class="bi bi-play"></i>
        </button>
    </div>
    @if ($allowPlaybackRate)
        <div class="mt-4" style="margin-left:auto;margin-right:auto">
            <label for="audioRange-{{ $id }}" class="form-label">Audio Speed: <span id="speed-value-{{ $id }}">1</span></label>
            <input type="range" class="form-range audioRange" min="0.5" max="1.5" step="0.05" id="audioRange-{{ $id }}" value="1">
        </div>
        <div class="d-flex justify-content-between" style="margin-left:auto;margin-right:auto">
            <small style="color:#bfbfbf">0.5</small>
            <small style="color:#bfbfbf">1</small>
            <small style="color:#bfbfbf">1.5</small>
        </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/nosleep.js@0.12.0/dist/NoSleep.min.js"></script>
<script>
(function() {
    const id = '{{ $id }}';
    const allowSeek = @json((bool) $allowSeek);
    const allowPlaybackRate = @json((bool) $allowPlaybackRate);
    const noSleep = new NoSleep();
    const player = $("#player-" + id);
    let watchedTime = 0;
    let hasBeenPlayed = false;

    // function to update ui based on time/duration
    const updatePlayerUI = (currentTime, duration) => {
        const sliderEl = player.find('.audio__slider');
        const circle = player.find('#seekbar');
        const getCircle = circle.get(0);
        const totalLength = getCircle.getTotalLength();

        if (!Number.isFinite(duration) || duration <= 0) {
            sliderEl.roundSlider('setValue', 0);
            circle.attr('stroke-dashoffset', totalLength);
        } else {
            const percent = (currentTime / duration) * 100;
            sliderEl.roundSlider('setValue', percent);

            const calc = totalLength - (currentTime / duration) * totalLength;
            circle.attr('stroke-dashoffset', calc);
        }

        // handle element visibility and interactivity
        const sliderApi = sliderEl.data('roundSlider');
        if (sliderApi) {
            // show the handle if played once
            const canShowHandle = hasBeenPlayed;
            const handle = sliderEl.find('.rs-handle');
            if (handle.length) {
                if (canShowHandle) {
                    handle.removeClass('hidden-handle').addClass('visible-handle');
                } else {
                    handle.removeClass('visible-handle').addClass('hidden-handle');
                }
            }

            // seeking is only possible if the handle is visible
            // if user can seek, remove readOnly on slider
            const canUserSeek = canShowHandle && Number.isFinite(duration) && duration > 0;
            sliderApi.option('readOnly', !canUserSeek);
        }
    };


    // initialize playback rate UI
    if (allowPlaybackRate) {
        const audioEl = document.getElementById("audio-" + id);
        const playbackRateValue = document.getElementById("speed-value-" + id);
        const playbackRateRange = document.getElementById("audioRange-" + id);
        if (playbackRateRange && playbackRateValue && audioEl) {
            playbackRateRange.addEventListener('input', function() {
                playbackRateValue.textContent = playbackRateRange.value;
                audioEl.playbackRate = parseFloat(playbackRateRange.value);
            });
        }
    }

    // initialize the circular slider scoped to this player
    const $slider = player.find('.audio__slider');
    $slider.roundSlider({
        radius: 50,
        value: 0,
        startAngle: 90,
        width: 10,
        handleSize: "+15",
        handleShape: "round",
        sliderType: "min-range",
        step: 0.1,
        readOnly: true // readOnly until audio starts playing
    });

    $slider.on('drag change', function(e) {
        const audioDom = player.find('audio')[0];
        const sliderEl = $(this);

        // should not fire, but safeguard against drag/change events when readOnly
        if (sliderEl.data('roundSlider').option('readOnly')) {
            console.log(`[Player ${id}] Slider interaction blocked: readOnly is true.`);
            return;
        }

        // derive slider value robustly across roundSlider event shapes
        const desiredPercent = (e && typeof e.value === 'number') ? e.value : 0;

        const duration = audioDom.duration || 0;
        if (!Number.isFinite(duration) || duration <= 0) {
            console.warn(`[Player ${id}] Cannot seek: duration is invalid or zero (${duration}).`);
            return;
        }

        let targetTime = (desiredPercent * duration) / 100;

        // check for if watchedTime is < targetTime
        // allowSeek is false, so limiting seek by watchedTime
        if (!allowSeek && typeof watchedTime !== 'undefined' && targetTime > watchedTime) {
            targetTime = watchedTime;
            // snap ui to watchedTime if forced
            updatePlayerUI(targetTime, duration);
        }

        audioDom.currentTime = targetTime;
    });


    initAudioPlayer(player);

    function initAudioPlayer(player) {
        let audio = player.find("audio"),
            play = player.find(".play-pause"),
            icon = player.find("#icon"),
            circle = player.find("#seekbar"),
            getCircle = circle.get(0),
            totalLength = getCircle.getTotalLength();

        circle.attr({
            "stroke-dasharray": totalLength,
            "stroke-dashoffset": totalLength
        });

        // helper function for pausing audio - overwritten below
        function pauseAudio() {
            audio[0].pause();
        }

        // play audio
        function playAudio() {
            if (!hasBeenPlayed) {
                hasBeenPlayed = true;
            }

            // pause all other audio players
            $("audio").each((index, el) => {
                if (el !== audio[0] && !el.paused) {
                    el.pause();
                }
            });
            $(".js-audio").not(player).removeClass("playing");

            noSleep.enable();

            audio[0].play().then(() => {
                player.removeClass("paused");
                icon.removeClass("bi-play");
                player.addClass("playing");
                icon.addClass("bi-pause");
                updatePlayerUI(audio[0].currentTime, audio[0].duration);
            }).catch(error => {
                console.error(`[Player ${id}] Audio play() failed:`, error);
                pauseAudio();
            });
        }

        play.on("click", () => {
            if (audio[0].paused) {
                playAudio();
            } else {
                pauseAudio();
            }
        });

        // overwriting native pause - catches all pause events
        const originalPause = audio[0].pause;
        audio[0].pause = function() {
            if (audio[0].paused) return;
            noSleep.disable();
            originalPause.apply(this);
            player.removeClass("playing");
            icon.removeClass("bi-pause");
            player.addClass("paused");
            icon.addClass("bi-play");
            updatePlayerUI(audio[0].currentTime, audio[0].duration);
        };

        audio.on("timeupdate", () => {
            let currentTime = audio[0].currentTime,
                maxduration = audio[0].duration;

            updatePlayerUI(currentTime, maxduration);

            if (currentTime > watchedTime) {
                watchedTime = currentTime;
            }
        });

        audio.on("ended", () => {
            noSleep.disable();

            player.removeClass("playing");
            icon.removeClass("bi-pause");
            icon.addClass("bi-play");

            // reset ui to 0
            updatePlayerUI(0, 0);

            // notify page-level completion handler if available
            if (typeof window.activityComplete === 'function') {
                try { window.activityComplete(); } catch (e) { /* noop */ }
            }
        });

        // update ui after seek to ensure it is synced
        audio.on("seeked", () => {
            updatePlayerUI(audio[0].currentTime, audio[0].duration);
        });
        audio.on("loadedmetadata", () => {
            updatePlayerUI(audio[0].currentTime, audio[0].duration);
        });

        updatePlayerUI(0, 0);
    }
})();
</script>