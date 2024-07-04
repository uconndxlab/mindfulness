@extends('layouts.main')

@section('title', $activity->title)

@section('content')
<div class="col-md-8">
    <div class="text-left">
        @php
            if ($activity->end_behavior == 'quiz') {
                $redirect_label = "QUIZ";
                $redirect_route = route('explore.quiz', ['quiz_id' => $activity->quiz->id]);
            }
            else if ($activity->end_behavior == "journal") {
                $redirect_label = "JOURNAL";
                $redirect_route = route('journal', ['activity' => $activity->id]);
            }
            else if (!isset($activity->next)) {
                $redirect_label = "FINISH";
                $redirect_route = route('explore.home');
            }
            else {
                $redirect_label = "NEXT";
                $redirect_route = route('explore.activity', ['activity_id' => $activity->next]);
            }
        @endphp

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
                    <a id="exit_btn" class="btn btn-link" href="{{ route('explore.week', ['week_id' => $activity->day->week->id]) }}">
                        <i id="exit_icon" class="bi bi-x-lg"></i>
                    </a>
                </h1>
            </div>
        </div>

        <h2>{{ $activity->sub_header }}</h2>
        <p>{{ $activity->description }}</p>

    </div>
    <div class="manual-margin-top">
        @php
            $content = $activity->content ? $activity->content : false
        @endphp

        @if ($content)
            <div id="content_main" class="content-main" data-type="{{ $content->type }}" style="display: block;">
                <x-contentView id="content_view" id2="pdf_download" type="{{ $content->type }}" file="{{ $content->file_name }}"/>
            </div>

            @if($content->completion_message != null)
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

