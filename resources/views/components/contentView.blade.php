@if ($type == 'audio')
    <audio id="{{ $id }}" class="media-player" controls preload="auto" src="{{ Storage::url('content/'.$file) }}"></audio>
@elseif ($type == 'video')
    <video id="{{ isset($id) ? $id : '' }}" class="media-player" controls preload="auto" width="100%" height="auto">
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
