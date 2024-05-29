@extends('layouts.main')

@section('title', $lesson->title)

@section('content')
<div class="col-md-8">
    <div class="text-left">
        @php
            if ($lesson->end_behavior == 'quiz') {
                $redirectLabel = "QUIZ";
                $redirectRoute = route('explore.quiz', ['quizId' => $quizId]);
            }
            else if ($lesson->end_behavior == "journal") {
                $redirectLabel = "JOURNAL";
                $redirectRoute = route('journal', ['activity' => $lesson->id]);
            }
            else {
                $redirectLabel = "FINISH ACTIVITY";
                $redirectRoute = route('explore.browse');
            }
            $progress = Auth::user()->progress;
        @endphp

        <h1 class="display fw-bold">{{ $lesson->title }}</h1>
        @if($lesson->sub_header)
            <h2>{{ $lesson->sub_header }}</h2>
        @endif
        @if($lesson->description)
            <p>{{ $lesson->description }}</p>
        @endif
    </div>

    <div class="container manual-margin-top">
        @if($main != null)
            <x-contentView id="content_main" id2="pdf_download" type="{{ $main->type }}" file="{{ $main->file_name }}"/>
            @if($main->completion_message != null)
                <div id="comp_message" class="mt-1" style="display: none;">
                    <pre class="text-success">{{ $main->completion_message }}</pre>
                </div>
            @endif
        @endif
    </div>

    <div class="container manual-margin-top">
        <a id="redirect_button" class="btn btn-primary disabled" href="{{ $redirectRoute }}">{{ $redirectLabel }}</a>
    </div>

    <div id="extra" class="container manual-margin-top mb-3" style="display: none;">
        @if($extra != null)
        <h3>Additional items:</h3>
            @foreach ($extra as $index => $item)
                @if (isset($item->name))
                    <h5>{{ $item->name }}</h5>
                @endif
                <x-contentView id="content_{{ $index }}" id2="pdf_download_{{ $index }}" type="{{ $item->type }}" file="{{ $item->file_name }}" />
            @endforeach
        @endif
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    //get element for content, get type
    const mainContent = document.getElementById('content_main')
    const mainType = '{{ $main ? $main->type : null }}'

    //if it is a pdf, get the element to set listener later
    let pdfDownload = null
    if (mainType && mainType == 'pdf') {
        pdfDownload = document.getElementById('pdf_download')
    }

    //other elements
    const redirectButton = document.getElementById('redirect_button')
    const extraDiv = document.getElementById('extra')

    //to track progress
    const lessonId = {{ $lesson->id }}

    //completion message
    const hasMessage = {{ $main && $main->completion_message ? 'true' : 'false' }} == true
    let completionMessageDiv = null
    if (hasMessage) {
        completionMessageDiv = document.getElementById('comp_message');
    }

    //checking if this lesson has already been completed
    const progress = {{ $progress }};
    const order = {{ $lesson->order }};
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
        //show message only when the activity is completed in that moment
        if (hasMessage) {
            completionMessageDiv.style.display = 'block';
        }

        // axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        //update users progress with lessonId
        if (progress <= order) {
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
    }
    
    //setting event listener based on type of content
    if (mainType) {
        if (mainType == 'pdf') {
            pdfDownload.addEventListener('click', () => {
                activityComplete();
            });
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
