@if ($type == 'audio')
    <div class="slide__audio js-audio col-12">
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
    <div class="col-4 mt-4" style="margin-left:auto;margin-right:auto">
    <label for="audioRange" class="form-label">Audio Speed:</label>
    <input type="range" class="form-range" min="0.5" max="1.5" step="0.05" id="audioRange">
    </div>
    <script>
        /*audio speed bar*/
        let aud = document.getElementsByTagName("audio")[0];
        let audRange = document.getElementById("audioRange");
        audRange.onchange = function(){aud.playbackRate = audRange.value}
        /*end audio speed bar*/

        var allowSeek = {{ $allowSeek }} == 'true' ? true : false;

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
    </script>
@elseif ($type == 'video')
    <video id="{{ isset($id) ? $id : '' }}" class="media-player video-player" controls controlsList="{{ isset($controlsList) ? $controlsList : '' }}" preload="auto" width="100%" height="auto">
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
@elseif ($type == 'image')
    <div style="display: flex; justify-content: center; align-items: center;">
        <span style="text-align: center;">
            <img id="{{ isset($id) ? $id : '' }}" src="{{ Storage::url('content/'.$file) }}" alt="Image">
            <br>
            <button id="img_complete_activity" class="btn btn-workbook mt-3" style="display: none;">GOT IT! WILL DO</button>
        </span>
    </div>
@elseif ($type == 'feedback_audio')
    <audio id="{{ $id }}" class="media-player feedback-audio" preload="auto" src="{{ Storage::url('content/'.$file) }}" controls onerror="alert('Error loading audio file');">
        Your browser does not support the audio element.
    </audio>
@endif