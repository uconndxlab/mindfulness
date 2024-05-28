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
            $progress = Auth::user()->progress;
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
            <x-contentView id="content_main" type="{{ $main->type }}" file="{{ $main->file_name }}"/>
        @endif
    </div>

    <div class="container manual-margin-top">
        <a id="redirectButton" class="btn btn-primary disabled" href="{{ $redirectRoute }}">{{ $redirectLabel }}</a>
    </div>

    <div id="extra" class="container manual-margin-top" style="display: none;">
        @if($extra != null)
        <h3>Additional items:</h3>
            @foreach ($extra as $index => $item)
                @if (isset($item->name))
                    <h5>{{ $item->name }}</h5>
                @endif
                <x-contentView id="content_{{ $index }}" type="{{ $item->type }}" file="{{ $item->file_name }}" />
            @endforeach
        @endif
    </div>


</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    const mainContent = document.getElementById('content_main')
    const mainType = '{{ $main ? $main->type : null }}'
    const redirectButton = document.getElementById('redirectButton')
    const extraDiv = document.getElementById('extra')
    const lessonId = {{ $lesson->id }}

    //checking if this has already been completed
    const progress = {{ $progress }}
    const order = {{ $lesson->order }}
    if (progress > order) {
        //if completed, show extra content and redirect
        extraDiv.style.display = 'block';
        redirectButton.classList.remove('disabled');
    }

    //call when activity finishes
    function activityComplete() {
        //show content
        redirectButton.classList.remove('disabled');
        extraDiv.style.display = 'block';

        // axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        //update users progress with lessonId
        axios.put('{{ route('user.update.progress') }}', {
            lessonId: lessonId
        })
        .then(response => {
            console.log(response.data.message);
        })
        .catch(error => {
            console.error('There was an error updating the progress:', error);
        });
    }
    
    //setting event listener based on type of content
    if (mainType) {
        if (mainType == 'pdf') {
            mainContent.addEventListener('click', () => {
                activityComplete();
            });
        }
        else {
            mainContent.addEventListener('ended', () => {
                activityComplete();
            });
        }
    }
</script>
@endsection
