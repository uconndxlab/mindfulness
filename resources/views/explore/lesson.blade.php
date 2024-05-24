@extends('layouts.main')

@section('title', $lesson->title)

@section('content')
<div class="col-md-8">
    <div class="text-left">
        @php
            if ($lesson->end_behavior == 'quiz') {
                $redirectLabel = "TAKE QUIZ";
                $redirectRoute = route('explore.quiz', ['quizId' => $quizId]);
            }
            else if ($lesson->end_behavior == "journal") {
                $redirectLabel = "JOURNAL";
                $redirectRoute = route('journal');
            }
            else {
                $redirectLabel = "FINISH ACTIVITY";
                $redirectRoute = route('explore.browse');
            }
        @endphp

        <h1 class="display font-weight-bold">{{ $lesson->title }}</h1>
        @if($lesson->sub_header)
            <h2>{{ $lesson->sub_header }}</h2>
        @endif
        @if($lesson->description)
            <p>{{ $lesson->description }}</p>
        @endif
    </div>

    <div class="container manual-margin-top">
        @if($main != null)
            <audio id="audio" controls preload="auto" src="{{ Storage::url('content/'.$main->file_name) }}"></audio>
        @endif
    </div>

    <div class="container manual-margin-top">
        <a id="redirectButton" class="btn btn-primary" href="{{ $redirectRoute }}">{{ $redirectLabel }}</a>
    </div>

    <div id="extra" class="container manual-margin-top" style="display: none;">
        @if($extra != null)
        <h3>Additional items:</h3>
            @foreach ($extra as $index => $item)
                @if (isset($item->name))
                    <h5>{{ $item->name }}</h5>
                @endif
                <audio id="audio_{{ $index }}" controls preload="auto">
                    <source src="{{ Storage::url('content/'.$item->file_name) }}" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
            @endforeach
        @endif
    </div>


</div>
<script>
    const audio = document.getElementById('audio')
    // var seekSlider = document.getElementById('seekSlider');
    // var playPauseBtn = document.getElementById('playPauseBtn');
    const redirectButton = document.getElementById('redirectButton')
    const extraDiv = document.getElementById('extraDiv')

    //disabling the redirection button
    redirectButton.classList.add('disabled');

    audio.addEventListener('ended', () => {
        redirectButton.classList.remove('disabled');
        extraDiv.style.display = 'block';
    });

    // //initializing the seek bar, waiting for audio to load
    // function initSeekBar() {
    //     var duration = Math.floor(audio.duration);
    //     seekSlider.max = duration;
    // }
    // audio.addEventListener('loadedmetadata', initSeekBar);

    // //play/pause functionality
    // function togglePlayPause() {
    //     if (audio.paused) {
    //         audio.play();
    //         playPauseBtn.textContent = 'Pause';
    //     } else {
    //         audio.pause();
    //         playPauseBtn.textContent = 'Play';
    //     }
    // }
    // playPauseBtn.addEventListener('click', togglePlayPause);

    // //seek and audio time relationship
    // function seek() {
    //     var seekto = audio.duration * (seekSlider.value / 100);
    //     audio.currentTime = seekto;
    // }
    // function updateSeekSlider() {
    //     var currentTime = Math.floor(audio.currentTime);
    //     seekSlider.value = currentTime;
    // }
    // audio.addEventListener('timeupdate', updateSeekSlider);
    // seekSlider.addEventListener('input', seek);
</script>
@endsection



<!-- style="display: none;" -->
<!-- <div class="audio-controls">
                <input type="range" id="seekSlider" value="0" min="0" step="1">
                <button id="playPauseBtn">Play</button>
                <div id="seekTime">0:00 / 0:00</div>
            </div> -->
