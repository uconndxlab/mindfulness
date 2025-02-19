<div id="player-{{ $id }}" class="slide__audio js-audio col-12">
    <audio id="audio-{{ $id }}" class="slide__audio-player media-player" controlsList="{{ isset($controlsList) ? $controlsList : '' }}" preload="auto" src="{{ Storage::url('content/'.$file) }}">
    </audio>
    @php
        // check if playbackrate is allowed in controlslist
        $controlsArray = explode(" ", $controlsList);
        $noPlaybackRate = in_array("noplaybackrate", $controlsArray);
    @endphp
    <div class="audio__controls">
        <svg version="1.1" id="circle" width="306px" height="306px" viewBox="0 0 100 100">
            <path id="seekbar" fill="none" stroke-meterlimit="10" d="M50,2.9L50,2.9C76,2.9,97.1,24,97.1,50v0C97.1,76,76,97.1,50,97.1h0C24,97.1,2.9,76,2.9,50v0C2.9,24,24,2.9,50,2.9z"/>
        </svg>
        <div class="audio__slider"></div>
        <button class="play-pause">
            <i id="icon" class="bi bi-play"></i>
        </button>
    </div>
</div>
@if (!$noPlaybackRate)
    <div id="audio-range-{{ $id }}">
        <div class="col-4 mt-4" style="margin-left:auto;margin-right:auto">
            <label for="audioRange" class="form-label">Audio Speed: <span id="speed-value">1</span></label>
            <input type="range" class="form-range" min="0.5" max="1.5" step="0.05" id="audioRange">
        </div>
        <div class="col-4 d-flex justify-content-between" style="margin-left:auto;margin-right:auto">
            <small style="color:#bfbfbf">0.5</small>
            <small style="color:#bfbfbf">1</small>
            <small style="color:#bfbfbf">1.5</small>
        </div>
    </div>
@endif
<script>
(function() {
    // get the id
    let id = '{{ $id }}';
    // init the audioplayer
    initAudioPlayer($("#player-"+id));

    // check if playbackrate is allowed in controlslist
    let noPlaybackRate = '{{ $noPlaybackRate }}' == 'true' ? true : false;
    if (!noPlaybackRate) {
        let aud = document.getElementsByTagName("audio")[0];
        let audRange = document.getElementById("audioRange");
        if (audRange) {
            audRange.onchange = function() {
                document.getElementById("speed-value").innerHTML=audRange.value;
                aud.playbackRate = audRange.value
            }
        }
    }
    /*end audio speed bar*/

    var allowSeek = {{ $allowSeek }} == 'true' ? true : false;

    $(".audio__slider").roundSlider({
        radius: 50,
        value: 0,
        startAngle: 90,
        width: 10,
        handleSize: "+15",
        handleShape: "round",
        sliderType: "min-range",
        step:0.1
    });

    $(".audio__slider").on("drag, change", function (e) {
        let $this = $(this);
        let $elem = $this.closest(".js-audio");
        updateAudio(e, $elem);
        $this.addClass("active");
    });

    function updateAudio(e, $elem) {
        let value = e.handle.value;
        // var thisPlayer = el.find('.js-audio'),
        var play = $elem.find(".play-pause"),
            circle = $elem.find("#seekbar"),
            getCircle = circle.get(0),
            totalLength = getCircle.getTotalLength(),
            //currentTime = $elem.find('audio')[0].currentTime,
            maxduration = $elem.find("audio")[0].duration;
        var y = (value * maxduration) / 100;
        $elem.find("audio")[0].currentTime = y;
    }

    function initAudioPlayer(player) {
        console.log("Initializing audio player " + "{{ $id }}");
        let watchedTime = 0;
        let audio = player.find("audio"),
            play = player.find(".play-pause"),
            icon = player.find("#icon"),
            circle = player.find("#seekbar"),
            getCircle = circle.get(0),
            totalLength = getCircle.getTotalLength();
        
        //if seek is allowed, just set watched to duration
        if (allowSeek) {
            watchedTime = audio[0].duration;
        }

        circle.attr({
            "stroke-dasharray": totalLength,
            "stroke-dashoffset": totalLength
        });

        // pause audio
        function pauseAudio() {
            audio[0].pause();
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
            var slider = audio.closest(".js-audio").find(".audio__slider");
            $(slider).roundSlider("setValue", value);
            //updating watch time to allow user to seek back to the last watched time
            if (currentTime > watchedTime) {
                watchedTime = currentTime;
            }
        });

        audio.on("ended", () => {
            player.removeClass("playing");
            icon.removeClass("bi-pause");
            icon.addClass("bi-play");
            circle.attr("stroke-dashoffset", totalLength);
        });

        audio.on("seeking", (e) => {
            //blocking the user from seeking forward beyond watchedtime
            let currentTime = audio[0].currentTime;
            if (currentTime > watchedTime && !allowSeek) {
                audio[0].currentTime = watchedTime;
                e.preventDefault();
            }
        });

        audio.on("seeked", () => {
            isSeeking = false;
        });
    }
})();
</script>