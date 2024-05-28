@if ($type == 'audio')
    <audio id="{{ $id }}" controls preload="auto" src="{{ Storage::url('content/'.$file) }}"></audio>
@elseif ($type == 'video')
    <video id="{{ $id }}" controls preload="auto" width="100%" height="auto">
        <source src="{{ Storage::url('content/'.$file) }}" type="video/mp4">
        Your browser does not support the video element.
    </video>
@elseif ($type == 'pdf')
    <div>
        <a id="{{ $id }}" class="btn btn-link" href="{{ Storage::url('content/'.$file) }}" target="_blank">Open workbook page</a>
    </div>
@endif
