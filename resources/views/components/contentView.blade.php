@if ($type == 'video')
    <video id="{{ isset($id) ? $id : '' }}" class="media-player video-player" controls preload="auto">
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
    <div class="d-flex justify-content-center align-items-center">
        <span class="text-center">
            <img id="{{ isset($id) ? $id : '' }}" src="{{ Storage::url('content/'.$file) }}" alt="Image" style="max-width: 100%; max-height: 70vh;">
            <br>
            <button id="img_complete_activity" class="btn btn-workbook mt-3 d-none">Got it! Will do.</button>
        </span>
    </div>
@elseif ($type == 'feedback_audio')
    <div class="feedback-audio js-audio col-12 mb-2">
        <audio id="{{ $id }}" class="feedback-aduio-player media-player" preload="auto" src="{{ Storage::url('content/'.$file) }}"></audio>
        <div class="feedback-audio__controls d-flex justify-content-between">
            <div class="d-flex">
                <button class="play-pause" type="button">
                    <i id="icon" class="bi bi-play"></i>
                </button>
                <div class="d-flex mt-2 ms-2"><small id="current-time"></small><small>/</small><small id="max-time"></small></div>
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
@endif