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

        <h1 class="display fw-bold">{{ $lesson->title }}
            <button id="favorite_btn" class="btn btn-link">
                <i id="favorite_icon" class="bi bi-star"></i>
            </button>
        </h1>

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
    //get lessonId
    const lessonId = {{ $lesson->id }};

    //MAIN
    //find the main content and its type
    const mainContent = document.getElementById('content_main');
    const mainType = '{{ $main ? $main->type : null }}';
    //if pdf, get the download element
    let pdfDownload = null;
    if (mainType && mainType == 'pdf') {
        pdfDownload = document.getElementById('pdf_download');
    }
    
    //FAVORITE
    //get favorite button, icon, isFavorited value
    const favButton = document.getElementById('favorite_btn');
    let isFavorited = {{ $isFavorited ? 'true' : 'false' }};
    const favIcon = document.getElementById('favorite_icon');
    if (isFavorited) {
        favIcon.className = 'bi bi-star-fill';
    }
    
    //COMPLETION ITEMS
    const redirectButton = document.getElementById('redirect_button');
    //completion message
    const hasMessage = {{ $main && $main->completion_message ? 'true' : 'false' }};
    let completionMessageDiv = hasMessage ? document.getElementById('comp_message') : null;
    //extra content
    const extraDiv = document.getElementById('extra');

    //CHECK COMPLETION
    const progress = {{ $progress }};
    const order = {{ $lesson->order }};
    if (progress > order) {
        //if completed, show extra content and redirect
        extraDiv.style.display = 'block';
        redirectButton.classList.remove('disabled');
    }

    //COMPLETION
    function activityComplete() {
        //show content and redirect
        redirectButton.classList.remove('disabled');
        extraDiv.style.display = 'block';
        //show message
        if (hasMessage) {
            completionMessageDiv.style.display = 'block';
        }
        //update users progress with lessonId
        if (progress <= order) {
            axios.put('{{ route('user.update.progress') }}', {
                lessonId: lessonId
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                console.log(response.data.message);
            })
            .catch(error => {
                console.error('There was an error updating the progress:', error);
            });
        }
    }
    
    //END LISTENER
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

    //FAVORITE HANDLING
    function addFavorite() {
        return new Promise((resolve, reject) => {
            axios.post('{{ route('favorites.create') }}', {
                lessonId: lessonId
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                console.log(response.data.message);
                resolve(true);
            })
            .catch(error => {
                console.error('There was an error adding favorite', error);
                reject(false);
            });
        });
    }

    function removeFavorite() {
        return new Promise((resolve, reject) => {
            axios.delete('/favorites/' + lessonId, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                console.log(response.data.message);
                resolve(true);
            })
            .catch(error => {
                console.error('There was an error removing favorite', error);
                reject(false);
            });
        });
    }

    //FAVORITE LISTENER
    favButton.addEventListener('click', () => {
        if (isFavorited) {
            removeFavorite().then(success => {
                if (success) {
                    isFavorited = false;
                    favIcon.className = "bi bi-star";
                }
            });
        }
        else {
            addFavorite().then(success => {
                if (success) {
                    isFavorited = true;
                    favIcon.className = "bi bi-star-fill";
                }
            });
        }
    });
</script>
@endsection
