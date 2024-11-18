@if ($type == 'audio')
    <div class="slide__audio js-audio col-12">
        <audio id="{{ $id }}" class="slide__audio-player media-player" controlsList="{{ isset($controlsList) ? $controlsList : '' }}" preload="auto" src="{{ Storage::url('content/'.$file) }}">
        </audio>
        @php
            // check if playbackrate is allowed in controlslist
            $controlsList = explode(" ", $controlsList);
            $noPlaybackRate = in_array("noplaybackrate", $controlsList);
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
        <div class="col-4 mt-4" style="margin-left:auto;margin-right:auto">
            <label for="audioRange" class="form-label">Audio Speed: <span id="speed-value">1</span></label>
            <input type="range" class="form-range" min="0.5" max="1.5" step="0.05" id="audioRange">
        </div>
        <div class="col-4 d-flex justify-content-between" style="margin-left:auto;margin-right:auto">
            <small style="color:#bfbfbf">0.5</small>
            <small style="color:#bfbfbf">1</small>
            <small style="color:#bfbfbf">1.5</small>
        </div>
    @endif
    <script>
        /*audio speed bar*/
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
            <a id="{{ isset($id2) ? $id2 : '' }}" class="btn btn-primary btn-workbook" href="{{ Storage::url('content/'.$file) }}" download>DOWNLOAD WORKBOOK <i class="bi bi-download"></i></a>
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
    <div class="feedback-audio js-audio col-12 mb-2">
        <audio id="{{ $id }}" class="feedback-aduio-player media-player" controlsList="{{ isset($controlsList) ? $controlsList : '' }}" preload="auto" src="{{ Storage::url('content/'.$file) }}"></audio>
        <div class="feedback-audio__controls d-flex justify-content-between">
            <div class="d-flex">
                <button class="play-pause">
                    <i id="icon" class="bi bi-pause"></i>
                </button>
                <div class="d-flex" style="margin-top:3px;margin-left:10px"><small id="current-time"></small><small>/</small><small id="max-time"></small></div>
            </div>
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Audio Speed
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-speed="0.5">0.5x</a></li>
                    <li><a class="dropdown-item" href="#" data-speed="0.75">0.75x</a></li>
                    <li><a class="dropdown-item" href="#" data-speed="1">1x (Normal)</a></li>
                    <li><a class="dropdown-item" href="#" data-speed="1.25">1.25x</a></li>
                    <li><a class="dropdown-item" href="#" data-speed="1.5">1.5x</a></li>
                </ul>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        $(".js-audio").each(function() {
        initAudioPlayer($(this));
    });

    // playback speed dropdown
    $('.dropdown-item').on('click', function(event) {
        event.preventDefault();  
        
        const speed = parseFloat($(this).attr('data-speed'));  
        const audio = $(this).closest('.js-audio').find('audio')[0];  
        
        if (audio) {
            audio.playbackRate = speed;
            console.log("Playback speed set to:", speed);
            
            $(this).closest('.dropdown').find('.dropdown-toggle').text(`Audio Speed: ${speed}x`);
        } else {
            console.error("Audio element not found!");
        }
        });
    });

function initAudioPlayer(player) {
    let audio = player.find("audio")[0];  
    let play = player.find(".play-pause");  
    let icon = player.find("#icon"); 
    let isPlaying = false;

    // Use onloadmetadata to get max duration
    audio.onloadedmetadata = function() {
        if (audio.duration) {
            player.find("#max-time").html(formatTime(audio.duration));  
        }
    };

    // Listen for play
    audio.addEventListener('play', function() {
        if (!isPlaying) {
            player.removeClass("paused").addClass("playing");
            icon.removeClass("bi-play").addClass("bi-pause");
            isPlaying = true;  // Mark as playing
        }
    });

    // Listen for pause
    audio.addEventListener('pause', function() {
        if (isPlaying) {
            player.removeClass("playing").addClass("paused");
            icon.removeClass("bi-pause").addClass("bi-play");
            isPlaying = false;  // Mark as paused
        }
    });

    // Pause/Play on click
    play.on("click", function() {
        if (audio.paused) {
            playAudio(player, icon, audio, isPlaying);
        } else {
            pauseAudio(player, icon, audio, isPlaying);
        }
    });

    // Update current time on every time update
    audio.ontimeupdate = function() {
        player.find("#current-time").html(formatTime(audio.currentTime));
    };

    // When audio ends, reset player state to paused
    audio.onended = function() {
        pauseAudio(player, icon, audio, isPlaying);
    };

    if (!audio.paused) {
        setTimeout(function() {
            audio.dispatchEvent(new Event('play'));
        }, 50);
    }
}

// Play audio and update icon/state
function playAudio(player, icon, audio, isPlaying) {
    if (isPlaying) return;

    $("audio").each(function() {
        this.pause();
    });

    $(".js-audio").removeClass("playing").addClass("paused");
    $(".play-pause").each(function() {
        $(this).find("#icon").removeClass("bi-pause").addClass("bi-play");
    });

    audio.play();
    player.removeClass("paused").addClass("playing");
    icon.removeClass("bi-play").addClass("bi-pause");
    isPlaying = true;
}

// Pause audio and update icon/state
function pauseAudio(player, icon, audio, isPlaying) {
    if (!isPlaying) return;

    audio.pause();
    player.removeClass("playing").addClass("paused");
    icon.removeClass("bi-pause").addClass("bi-play");
    isPlaying = false;
}

// Format time
function formatTime(timeInSeconds) {
    let minutes = Math.floor(timeInSeconds / 60);
    let seconds = Math.floor(timeInSeconds % 60);
    return `${minutes}:${seconds.toString().padStart(2, "0")}`;
}

    </script>
@endif