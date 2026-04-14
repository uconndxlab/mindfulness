@props([
    'presetTime' => 5,
    'completeOnFinish' => false,
])

<div id="timer-container" class="timer-container" 
     data-preset-time="{{ $presetTime }}"
     data-complete-on-finish="{{ $completeOnFinish ? 'true' : 'false' }}">
    <div class="timer-layout">
        <div id="timer-controls" class="timer-controls">
            <button type="button" id="timer-reset" class="btn btn-secondary d-none">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button type="button" id="timer-play-pause" class="btn btn-primary">
                <i class="bi bi-play"></i>
            </button>
        </div>
        <div id="timer-display" class="timer-display">{{ $presetTime }}:00</div>
    </div>
</div>
