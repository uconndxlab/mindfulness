@if ($type == 'audio')
    <audio id="{{ $id }}" controls preload="auto" src="{{ Storage::url('content/'.$file) }}"></audio>
@elseif ($type == 'video')
    <video id="{{ $id }}" controls preload="auto" width="100%" height="auto">
        <source src="{{ Storage::url('content/'.$file) }}" type="video/mp4">
        Your browser does not support the video element.
    </video>
@elseif ($type == 'pdf')
    <div>
        <span>
            <a id="{{ $id }}" class="btn btn-link" href="{{ Storage::url('content/'.$file) }}" target="_blank">Open workbook page</a>
            <a id="{{ $id2 }}" href="{{ Storage::url('content/'.$file) }}" download class="btn btn-info"><i class="bi bi-download"></i></a>
        </span>
    </div>
@endif
