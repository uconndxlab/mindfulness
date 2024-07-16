@extends('layouts.main')

@section('title', $activity->title)

@section('content')
<div class="col-md-8">
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <div class="text-left">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display fw-bold">{{ $activity->title }}
                    <button id="favorite_btn" class="btn btn-link">
                        <i id="favorite_icon" class="bi bi-star"></i>
                    </button>
                </h1>
            </div>
            <div>
                <h1 class="display fw-bold">
                    <a id="exit_btn" class="btn btn-link" href="{{ $page_info['exit_route'] }}">
                        <i id="exit_icon" class="bi bi-x-lg"></i>
                    </a>
                </h1>
            </div>
        </div>

        <h2>{{ $activity->sub_header }}</h2>
        <p>{{ $activity->description }}</p>

    </div>
    <div class="manual-margin-top">
        @if ($content)
            <div id="content_main" class="content-main" data-type="{{ $content->type }}" style="display: block;">
                <x-contentView id="content_view" id2="pdf_download" type="{{ $content->type }}" file="{{ $content->file_path }}"/>
            </div>

            @if($content->completion_message)
                <div id="comp_message" class="mt-1" style="display: none;">
                    <pre class="text-success">{{ $content->completion_message }}</pre>
                </div>
            @endif
        @endif
    </div>
    <div class="manual-margin-top" id="redirect_div">
        @if (isset($page_info['end_route']))
            <a id="redirect_button_2" class="btn btn-primary disabled" href="{{ $page_info['end_route'] }}">{{ $page_info['end_label'] }}</a>
        @endif
        @if (isset($page_info['redirect_route']))
            <a id="redirect_button" class="btn btn-primary disabled" href="{{ $page_info['redirect_route'] }}">{{ $page_info['redirect_label'] }}</a>
        @endif
        <a id="skip" class="btn btn-primary">skip</a>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    const activity_id = {{ $activity->id }};
    const optional = {{ $activity->optional }};

    //COMPLETION ITEMS
    const redirectDiv = document.getElementById('redirect_div');
    const hasContent = {{ $content ? 'true' : 'false' }};

    //SKIP
    const skip = document.getElementById('skip');
    skip.addEventListener('click', activityComplete)

    //set eventlisteners to call activityComplete
    if (hasContent) {
        const content = document.getElementById('content_view');
        const type = '{{ isset($content->type) ? $content->type : null }}';
        if (type == 'pdf') {
            const pdfDownload = document.getElementById('pdf_download');
            pdfDownload.addEventListener('click', activityComplete);
            content.addEventListener('click', activityComplete);
        }
        else {
            content.addEventListener('ended', activityComplete);
        }
    }
    else {
        //if no content
        //unlock redirect and add an event listener to track progress
        unlockRedirect(true);
    }

    //CHECKING COMPLETION
    const status = '{{ $activity->status }}';
    if (status == 'completed') {
        //if completed unlock the redirect button
        unlockRedirect();
    }

    //COMPLETION
    function activityComplete() {
        //show content and redirect
        console.log("activity completed")
        unlockRedirect();
        //show message
        const hasMessage = {{ isset($content->completion_message) ? 'true' : 'false' }};
        if (hasMessage) {
            const completionMessageDiv = document.getElementById('comp_message');
            completionMessageDiv.style.display = 'block';
        }
        //update users progress
        if (status == 'unlocked') {
            axios.put('{{ route('user.update.progress') }}', {
                activity_id: activity_id
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

    //function for unlocking the redirection buttons
    function unlockRedirect(addEventListener = false) {
        redirectDiv.querySelectorAll('.disabled').forEach(element => {
            element.classList.remove('disabled');

            //if no content
            if (addEventListener) {
                element.addEventListener('click', activityComplete);
            }
        });
    }

    //FAVORITES
    //get favorite button, icon, isFavorited value
    const favButton = document.getElementById('favorite_btn');
    let isFavorited = {{ $is_favorited ? 'true' : 'false' }};
    const favIcon = document.getElementById('favorite_icon');
    if (isFavorited) {
        favIcon.className = 'bi bi-star-fill';
    }

    //FAVORITE HANDLING
    function addFavorite() {
        return new Promise((resolve, reject) => {
            axios.post('{{ route('favorites.create') }}', {
                activity_id: activity_id
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
            axios.delete('/favorites/' + activity_id, {
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

