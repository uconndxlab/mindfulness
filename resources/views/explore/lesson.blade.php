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
            <audio id="audio_main" controls preload="auto" src="{{ Storage::url('content/'.$main->file_name) }}">
            </audio>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    const audio = document.getElementById('audio_main')
    const redirectButton = document.getElementById('redirectButton')
    const extraDiv = document.getElementById('extra')
    const lessonId = {{ $lesson->id }}

    //disabling the redirection button
    redirectButton.classList.add('disabled');

    audio.addEventListener('ended', () => {
        redirectButton.classList.remove('disabled');
        extraDiv.style.display = 'block';

        //update users progress
        // axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        axios.put('{{ route('user.update.progress') }}', {
            lessonId: lessonId
        })
        .then(response => {
            console.log(response.data.message);
        })
        .catch(error => {
            console.error('There was an error updating the progress:', error);
        });

    });
</script>
@endsection
