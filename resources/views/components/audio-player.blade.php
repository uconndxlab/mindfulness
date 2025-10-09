<div id="player-{{ $id }}" 
    class="slide__audio js-audio col-12" 
    data-allow-seek="{{ $allowSeek ? 'true' : 'false' }}" 
    data-allow-playback-rate="{{ $allowPlaybackRate ? 'true' : 'false' }}"
    data-title="{{ $title ?? 'Audio Track' }}"
    data-artist="{{ $artist ?? 'Unknown Artist' }}"
    data-artwork="{{ $artwork ?? '' }}">
    <audio id="audio-{{ $id }}" class="slide__audio-player media-player" preload="auto" src="{{ Storage::url('content/'.$file) }}" voice="{{ $id }}"></audio>
    <div class="audio__controls">
        <svg version="1.1" id="circle" width="300px" height="300px" viewBox="-5 -5 110 110">
            <path id="track" fill="none" stroke-meterlimit="10" d="M50,2.9L50,2.9C76,2.9,97.1,24,97.1,50v0C97.1,76,76,97.1,50,97.1h0C24,97.1,2.9,76,2.9,50v0C2.9,24,24,2.9,50,2.9z"/>
            <path id="watched-progress" fill="none" stroke-meterlimit="10" d="M50,2.9L50,2.9C76,2.9,97.1,24,97.1,50v0C97.1,76,76,97.1,50,97.1h0C24,97.1,2.9,76,2.9,50v0C2.9,24,24,2.9,50,2.9z"/>
            <path id="seekbar" fill="none" stroke-meterlimit="10" d="M50,2.9L50,2.9C76,2.9,97.1,24,97.1,50v0C97.1,76,76,97.1,50,97.1h0C24,97.1,2.9,76,2.9,50v0C2.9,24,24,2.9,50,2.9z"/>
        </svg>
        <button class="play-pause">
            <i id="icon" class="bi bi-play"></i>
        </button>
    </div>
    @if ($allowPlaybackRate)
        <div class="mt-4 mx-auto">
            <label for="audioRange-{{ $id }}" class="form-label">Audio Speed: <span id="speed-value-{{ $id }}">1</span></label>
            <input type="range" class="form-range audioRange" min="0.5" max="1.5" step="0.05" id="audioRange-{{ $id }}" value="1">
        </div>
        <div class="d-flex justify-content-between mx-auto">
            <small>0.5</small>
            <small>1</small>
            <small>1.5</small>
        </div>
    @endif
</div>
