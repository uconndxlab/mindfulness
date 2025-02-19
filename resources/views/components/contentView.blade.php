@if ($type == 'video')
    <video id="{{ isset($id) ? $id : '' }}" class="media-player video-player" controls controlsList="{{ isset($controlsList) ? $controlsList : '' }}" preload="auto" width="100%" height="auto">
        <source src="{{ Storage::url('content/'.$file) }}" type="video/mp4">
        Your browser does not support the video element.
    </video>
@elseif ($type == 'pdf')
    <div>
        <!-- class="link-workbook" -->
        <span>
            <a id="{{ isset($id) ? $id : '' }}" class="btn btn-primary btn-workbook" href="{{ Storage::url('content/'.$file) }}" target="_blank">Open workbook page <i class="bi bi-arrow-right"></i></a>
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
                <button class="play-pause" type="button">
                    <i id="icon" class="bi bi-play"></i>
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