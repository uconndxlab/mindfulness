@foreach ($question['options'] as $option)
    <div id="feedback_{{ $question['number'] }}_{{ $option['id'] }}" 
         data-show="{{ !empty($option['feedback']) ? 'true' : 'false' }}" 
         class="feedback-div mt-4 d-none">
         
        @if ($option['audio_path'])
            <div class="feedback-audio js-audio col-12 mb-2">
                <audio id="fbAudio_{{ $question['number'] }}_{{ $option['id'] }}" class="feedback-aduio-player media-player" preload="auto" src="{{ Storage::url('content/'.$option['audio_path']) }}"></audio>
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
        
        <div class="text-info feedback-text">
            @markdown(is_string($option['feedback'] ?? null) ? $option['feedback'] : '')
        </div>
    </div>
@endforeach
