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
        @if (!$main->isEmpty())
            @if ($main->count() > 1)
                <select id="voice_select" onchange="handleVoiceChange()">
                    @foreach($main as $index => $content)
                        <option value="{{ $index }}">{{ $content->voice ? $content->voice : "Other" }}</option>
                    @endforeach
                </select>
            @endif
                
            @foreach($main as $index => $content)
                <div id="content_main_{{ $index }}" class="content-main {{ $index == 0 ? 'initial-content' : '' }}" data-type="{{ $content->type }}" style="display: {{ $index == 0 ? 'block' : 'none' }};">
                    <x-contentView id="content_view_{{ $index }}" id2="pdf_download" type="{{ $content->type }}" file="{{ $content->file_name }}"/>
                </div>
            @endforeach
            @if($main->first()->completion_message != null)
                <div id="comp_message" class="mt-1" style="display: none;">
                    <pre class="text-success">{{ $main->first()->completion_message }}</pre>
                </div>
            @endif
        @endif
    </div>

    <div class="container manual-margin-top">
        <a id="redirect_button" class="btn btn-primary disabled" href="{{ $redirectRoute }}">{{ $redirectLabel }}</a>
    </div>

    <div id="extra" class="container manual-margin-top mb-3" style="display: none;">
        @if(!$extra->isEmpty())
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
    let currentContentMain = null;
    let activityCompleteHandler = function() {
        activityComplete();
    };
    
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
    let hasMessage = {{ $main->first() && $main->first()->completion_message ? 'true' : 'false' }};
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
        console.log("activity completed")
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

    //VOICE OPTIONS
    function handleVoiceChange() {
        //hide all options
        document.querySelectorAll('.content-main').forEach(function(element) {
            element.style.display = 'none';
        });

        //get select value
        var select = document.getElementById('voice_select');
        var selectedIndex = select.value;

        //show the content selected
        var selectedContent = document.getElementById('content_main_' + selectedIndex);
        if (selectedContent) {
            selectedContent.style.display = 'block';
            updateCurrentContentMain(selectedContent);
        }
    }

    //only used in voice change
    function updateCurrentContentMain(newElement) {
        if (currentContentMain) {
            //remove listener
            currentContentMain.removeEventListener('ended', activityCompleteHandler);
        }
        //get the audio element specifically
        currentContentMain = newElement.querySelector('audio');

        //add new listener
        currentContentMain.addEventListener('ended', activityCompleteHandler);
    }

    //ON LOAD
    document.addEventListener('DOMContentLoaded', function() {
        //check count
        const mainCount = {{ $main->count() }};
        //CASE WITH SELECT - AUDIO ONLY
        if (mainCount > 1) {
            const select = document.getElementById('voice_select');
            if (select) {
                handleVoiceChange();
            }
        }
        //CASE WHERE ONE ITEM
        else {
            //first main item
            const initialContent = document.querySelector('.content-main.initial-content');
            if (initialContent) {
                //only one item - get first index and its type
                currentContentMain = document.getElementById('content_view_0');
                const type = '{{ $main->count() == 1 ? $main->first()->type : null }}'
                //set listener accordingly
                if (type == "pdf") {
                    const pdfDownload = document.getElementById('pdf_download');
                    pdfDownload.addEventListener('click', activityCompleteHandler);
                    currentContentMain.addEventListener('click', activityCompleteHandler);
                }
                else {
                    console.log(type)
                    currentContentMain.addEventListener('ended', activityCompleteHandler);
                }
            }
        }
    });
</script>
@endsection
