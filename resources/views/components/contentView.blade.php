@if ($type == 'video')
    <video id="{{ isset($id) ? $id : '' }}" class="media-player video-player col-md-8" controls preload="auto">
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
    <div class="d-flex justify-content-center align-items-center content-view-image">
        <span class="text-center">
            <img id="{{ isset($id) ? $id : '' }}" src="{{ Storage::url('content/'.$file) }}" alt="Image">
            <br>
            <button id="img_complete_activity" class="btn btn-workbook mt-3 d-none">Got it! Will do.</button>
        </span>
    </div>
@endif