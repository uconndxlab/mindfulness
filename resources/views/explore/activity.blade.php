@extends('layouts.main')

@section('title', $activity->title)

@section('content')
<div class="col-md-8">
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
                    <a id="exit_btn" class="btn btn-link" href="{{ $exit_route }}">
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
    <div class=" manual-margin-top">
        <a id="redirect_button" class="btn btn-primary disabled" href="{{ $redirect_route }}">{{ $redirect_label }}</a>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    const activity_id = {{ $activity->id }};

    //COMPLETION ITEMS
    const redirectButton = document.getElementById('redirect_button');
    const hasContent = {{ $content ? 'true' : 'false' }};
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
        redirectButton.classList.remove('disabled');
        redirectButton.addEventListener('click', activityComplete);
    }

    //CHECKING COMPLETION
    const progress = {{ $progress }};
    const order = {{ $activity->order }};
    if (progress > order) {
        //if completed unlock the redirect button
        redirectButton.classList.remove('disabled');
    }

    //COMPLETION
    function activityComplete() {
        //show content and redirect
        console.log("activity completed")
        redirectButton.classList.remove('disabled');
        //show message
        const hasMessage = {{ isset($content->completion_message) ? 'true' : 'false' }};
        if (hasMessage) {
            const completionMessageDiv = document.getElementById('comp_message');
            completionMessageDiv.style.display = 'block';
        }
        //update users progress
        if (progress <= order) {
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

