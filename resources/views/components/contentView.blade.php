@if ($type == 'audio')
    <div class="slide__audio js-audio">
        <audio id="{{ $id }}" class="slide__audio-player media-player" controlsList="{{ isset($controlsList) ? $controlsList : '' }}" preload="auto" src="{{ Storage::url('content/'.$file) }}">
        </audio>
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
    <script>
        $(".js-audio").each(function (index, el) {
            initAudioPlayer($(this), index);
        });

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

            play.on("click", () => {
                if (audio[0].paused) {
                    $("audio").each((index, el) => {
                        $("audio")[index].pause();
                    });
                    $(".js-audio").removeClass("playing");
                    audio[0].play();
                    player.removeClass("paused");
                    icon.removeClass("bi-play");
                    player.addClass("playing");
                    icon.addClass("bi-pause");
                } else {
                    audio[0].pause();
                    player.removeClass("playing");
                    icon.removeClass("bi-pause");
                    player.addClass("paused");
                    icon.addClass("bi-play");
                }
            });

            audio.on("timeupdate", () => {
                let currentTime = audio[0].currentTime,
                    maxduration = audio[0].duration,
                    calc = totalLength - (currentTime / maxduration) * totalLength;
                circle.attr("stroke-dashoffset", calc);
                let value = ((currentTime / maxduration) * 100);
                var slider = audio.closest(".js-audio").find(".audio__slider");
                $(slider).roundSlider("setValue", value);
                timeTracking.watchedTime = currentTime;
            });

            audio.on("ended", () => {
                player.removeClass("playing");
                icon.removeClass("bi-pause");
                icon.addClass("bi-play");
                circle.attr("stroke-dashoffset", totalLength);
            });

            audio.on("seeking", (e) => {
                let currentTime = audio[0].currentTime;
                let delta = currentTime - timeTracking.watchedTime;
                if (delta > 0) {
                    audio[0].currentTime = timeTracking[lastUpdated];
                    e.preventDefault();
                }
            });

            audio.on("seeked", () => {
                isSeeking = false;
            });
        }
    </script>
@elseif ($type == 'video')
    <video id="{{ isset($id) ? $id : '' }}" class="media-player" controls controlsList="{{ isset($controlsList) ? $controlsList : '' }}" preload="auto" width="100%" height="auto">
        <source src="{{ Storage::url('content/'.$file) }}" type="video/mp4">
        Your browser does not support the video element.
    </video>
@elseif ($type == 'pdf')
    <div>
        <span>
            <a id="{{ isset($id) ? $id : '' }}" class="link-workbook" href="{{ Storage::url('content/'.$file) }}" target="_blank">Open workbook page</a>
            <br>
            <a id="{{ isset($id2) ? $id2 : '' }}" class="btn btn-workbook" href="{{ Storage::url('content/'.$file) }}" download>DOWNLOAD WORKBOOK <i class="bi bi-download"></i></a>
        </span>
    </div>
@endif