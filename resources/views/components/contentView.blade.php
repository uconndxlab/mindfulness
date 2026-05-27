@props([
    'id' => null,
    'id2' => null,
    'voiceId' => null,
    'type' => null,
    'file' => null,
    'allowSeek' => true,
    'controlsList' => null,
])

@if ($type == 'video')
    @php
        $seekAllowed = filter_var($allowSeek, FILTER_VALIDATE_BOOLEAN);
        if ($controlsList === null && ! $seekAllowed) {
            $controlsList = 'noseek nodownload noplaybackrate';
        }
    @endphp
    <div class="video-container w-100 d-flex justify-content-center">
        <video id="{{ $id ?? '' }}" class="media-player video-player" controls preload="auto" data-allow-seek="{{ $seekAllowed ? 'true' : 'false' }}"@if ($controlsList) controlsList="{{ $controlsList }}"@endif>
            <source src="{{ Storage::url('content/'.$file) }}" type="video/mp4">
            Your browser does not support the video element.
        </video>
    </div>
@elseif ($type == 'pdf')
    <div>
        <!-- class="link-workbook" -->
        <span>
            <a id="{{ isset($id) ? $id : '' }}" class="btn btn-primary btn-workbook" href="{{ Storage::url('content/'.$file) }}" target="_blank">Open workbook page <i class="bi bi-arrow-right"></i></a>
        </span>
    </div>
@elseif ($type == 'image')
    <div class="d-flex justify-content-center align-items-center content-view-image">
        <span class="text-center">
            <img id="{{ isset($id) ? $id : '' }}" src="{{ Storage::url('content/'.$file) }}" alt="Image">
            <br>
        </span>
    </div>
@endif