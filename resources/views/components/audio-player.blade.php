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
</div>
<script src="https://cdn.jsdelivr.net/npm/nosleep.js@0.12.0/dist/NoSleep.min.js"></script>
<script>
(function() {
    const id = '{{ $id }}';
    const allowSeek = @json((bool) $allowSeek);
    const allowPlaybackRate = @json((bool) $allowPlaybackRate);
    const noSleep = new NoSleep();
    const player = $("#player-" + id);

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
        step: 0.1
    });

    $slider.on('drag change', function(e) {
        const audioDom = player.find('audio')[0];
        const maxDuration = audioDom.duration || 0;
        const value = e.handle && typeof e.handle.value !== 'undefined' ? e.handle.value : 0;
        if (Number.isFinite(maxDuration) && maxDuration > 0) {
            audioDom.currentTime = (value * maxDuration) / 100;
            // snap progress ring immediately
            const circle = player.find('#seekbar');
            const getCircle = circle.get(0);
            if (getCircle && typeof getCircle.getTotalLength === 'function') {
                const totalLength = getCircle.getTotalLength();
                const calc = totalLength - (audioDom.currentTime / maxDuration) * totalLength;
                circle.attr('stroke-dashoffset', calc);
            }
        }
        $(this).addClass('active');
    });

    initAudioPlayer(player);

    function initAudioPlayer(player) {
        console.log("Initializing audio player " + "{{ $id }}");
        let watchedTime = 0;
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

        // pause audio
        function pauseAudio() {
            audio[0].pause();
            console.log("{{ $id }}: " + "noSleep disabled");
            // enable screen sleep
            noSleep.disable();
            player.removeClass("playing");
            icon.removeClass("bi-pause");
            player.addClass("paused");
            icon.addClass("bi-play");
        }
        // play audio
        function playAudio() {
            console.log("Playing audio " + "{{ $id }}");
            $("audio").each((index, el) => {
                $("audio")[index].pause();
            });
            $(".js-audio").removeClass("playing");

            // disable screen sleep
            console.log("{{ $id }}: " + "NoSleep enabled");
            noSleep.enable();
            
            // play and change classes/icons
            audio[0].play();
            player.removeClass("paused");
            icon.removeClass("bi-play");
            player.addClass("playing");
            icon.addClass("bi-pause");
        }

        play.on("click", () => {
            if (audio[0].paused) {
                playAudio();
            } else {
                pauseAudio();
            }
        });

        // overwrite pause function - method replacement
        // needs to be able to pause without the button
        const originalPause = audio[0].pause;
        audio[0].pause = function() {
            // check if playing
            if (audio[0].paused) {
                console.log("{{ $id }}: " + "already paused");
                return;
            }
            // enable screen sleep
            console.log("{{ $id }}: " + "NoSleep disabled");
            noSleep.disable();
            originalPause.apply(this);
            player.removeClass("playing");
            icon.removeClass("bi-pause");
            player.addClass("paused");
            icon.addClass("bi-play");
        };

        audio.on("timeupdate", () => {
            let currentTime = audio[0].currentTime,
                maxduration = audio[0].duration,
                calc = totalLength - (currentTime / maxduration) * totalLength;
            circle.attr("stroke-dashoffset", calc);
            let value = ((currentTime / maxduration) * 100);
            player.find('.audio__slider').roundSlider('setValue', value);
            //updating watch time to allow user to seek back to the last watched time
            if (currentTime > watchedTime) {
                watchedTime = currentTime;
            }
        });

        audio.on("ended", () => {
            // enable screen sleep
            console.log("{{ $id }}: " + "nosleep disabled");
            noSleep.disable();

            player.removeClass("playing");
            icon.removeClass("bi-pause");
            icon.addClass("bi-play");
            circle.attr("stroke-dashoffset", totalLength);

            // notify page-level completion handler if available
            if (typeof window.activityComplete === 'function') {
                try { window.activityComplete(); } catch (e) { /* noop */ }
            }
        });

        audio.on("seeking", (e) => {
            //blocking the user from seeking forward beyond watchedtime
            let currentTime = audio[0].currentTime;
            if (currentTime > watchedTime && !allowSeek) {
                audio[0].currentTime = watchedTime;
                e.preventDefault();
            }
        });
    }
})();
</script>