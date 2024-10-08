@extends('layouts.main')

@section('title', $activity->title)

@section('content')
<div class="col-md-8">
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <div id="error-messages" class="alert alert-danger" style="display: none;"></div>
    <div class="text-left">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="display fw-bold">{{ $activity->title }}
                <button id="favorite_btn" class="btn btn-link">
                    <i id="favorite_icon" class="bi bi-star"></i>
                </button>
            </h1>
        </div>
        <h5>{{ ucfirst($activity->type) }}</h5>

    </div>
    <div class="manual-margin-top">
        @if (($activity->type == 'practice' || $activity->type == 'lesson') && $content)
            @if (isset($content->instructions))
                <div class="text-left mb-3">
                    <h5>{!! $content->instructions !!}</h5>
                </div>
            @endif
            @php
                $controlsList = ($activity->type === 'practice' ? 'noplaybackrate' : '');
                $allowSeek = $activity->status == 'completed' ? 'true' : 'false';
            @endphp
            @if ($content->audio_options)
                <div class="col-6 mt-1" id="audio-options-div" style="display: none;">
                    @php
                        $display_voice = count($content->audio_options) > 1 ? 'block' : 'none';
                    @endphp
                    <div class="form-group dropdown" data-display="{{ $display_voice }}" style="display: {{ $display_voice }}">
                        <label class="fw-bold" for="voice_dropdown_button">Voice Selection:</label>
                        <!-- voice selection -->
                        <button id="voice_dropdown_button" class="btn btn-xlight dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ key($content->audio_options) }}
                        </button>
                        <ul class="dropdown-menu" id="voice_dropdown" name="voice_dropdown">
                            @foreach ($content->audio_options as $voice => $time_options)
                                <li>
                                    <button class="dropdown-item" type="button" value="{{ $voice }}" onclick="selectVoice('{{ $voice }}')">
                                        {{ $voice }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <input type="hidden" id="voice_select" name="voice_select" value="{{ key($content->audio_options) }}">
                    </div>
                    <!-- time selections -->
                    @foreach ($content->audio_options as $voice => $time_options)
                        @php
                            $display_time = count($time_options) > 1 ? 'block' : 'none';
                        @endphp
                        <div class="form-group dropdown time-dropdown" voice="{{ $voice }}" data-display="{{ $display_time }}" style="display: {{ $display_time }}">
                            <label class="fw-bold" for="time_dropdown_button_{{ $voice }}">Time:</label>
                            <button id="time_dropdown_button_{{ $voice }}" class="btn btn-xlight dropdown-toggle time-toggle" time="{{ key($time_options) }}" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ key($time_options) }} min
                            </button>
                            <ul class="dropdown-menu" id="time_dropdown_{{ $voice }}" name="time_dropdown_{{ $voice }}">
                                @foreach ($time_options as $time => $_)
                                    <li>
                                        <button class="dropdown-item" type="button" value="{{ $time }}" onclick="selectTime('{{ $time }}')">
                                            {{ $time }} min
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <input type="hidden" id="time_select" name="time_select" value="{{ key($time_options) }}">
                        </div>
                    @endforeach
                </div>

                @foreach ($content->audio_options as $voice => $time_options)
                    @foreach ($time_options as $time => $file_path)
                        <div id="content_main" class="content-main" voice="{{ $voice }}" time="{{ $time }}" data-type="audio" style="display: none;">
                            <x-contentView id="content_view" type="audio" file="{{ $file_path }}" controlsList="{{ $controlsList }}" allowSeek="{{ $allowSeek }}"/>
                        </div>
                    @endforeach
                @endforeach

                @else
                    <div id="content_main" class="content-main" data-type="{{ $content->type }}" style="display: flex; justify-content: center; align-items: center;">
                        <x-contentView id="content_view" id2="download_btn" type="{{ $content->type }}" file="{{ $content->file_path }}" controlsList="{{ $controlsList }}" allowSeek="{{ $allowSeek }}"/>
                    </div>
                @endif

        @elseif ($activity->type == 'reflection' && $quiz)
            <div id="quizContainer">
                <x-quiz :quiz="$quiz"/>
            </div>
        @elseif ($activity->type == 'journal' && $journal)
            <div id="journalContainer">
                <x-journal :journal="$journal"/>
            </div>
        @endif
        @if($activity->completion_message)
            <div id="comp_message" class="mt-1" style="display: none;">
                <p class="text-success">{!! $activity->completion_message !!}</p>
            </div>
        @endif
        <div id="bonus_message" class="mt-1" style="display: none;">
            <form id="bonusForm" action="{{ route('explore.module.bonus', ['module_id' => $activity->day->module_id]) }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="day_id_accordion" value="{{ $activity->day_id }}">
                <a class="text-success" href="#" onclick="document.getElementById('bonusForm').submit();">Click here to view the bonus activities<i class="bi bi-arrow-right"></i></a>
            </form>
        </div>
    </div>
    <div class="manual-margin-top" id="redirect_div">
        @if (isset($page_info['redirect_route']))
            <a id="redirect_button" class="btn btn-tertiary redirect-btn disabled" href="{{ $page_info['redirect_route'] }}" style="display: none;">
                {{ $page_info['redirect_label'] }}
            </a>
        @endif
        <a id="skip" class="btn btn-primary" onclick="activityComplete()">skip</a>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    const activity_id = {{ $activity->id }};
    const optional = {{ $activity->optional }};

    //COMPLETION ITEMS
    const redirectDiv = document.getElementById('redirect_div');
    const hasContent = {{ $content ? 'true' : 'false' }};
    const hasQuiz = {{ $quiz ? 'true' : 'false' }};
    const hasJournal = {{ $journal ? 'true' : 'false' }};
    var allowSeek = false;

    //CHECKING COMPLETION
    const status = '{{ $activity->status }}';
    if (status == 'completed') {
        allowSeek = true;
        activityComplete(false);
    }

    var type = null;
    //set eventlisteners to call activityComplete
    if (hasContent) {
        console.log('Type: content');
        //applies to all content items
        const content = document.getElementById('content_view');
        type = '{{ isset($content->type) ? $content->type : null }}';
        if (type == 'pdf') {
            const downloadButton = document.getElementById('download_btn');
            downloadButton.addEventListener('click', activityComplete);
            content.addEventListener('click', activityComplete);
        }
        //adding complete button for images
        else if (type == 'image' && status != 'completed') {
            const completeButton = document.getElementById('img_complete_activity');
            completeButton.classList.remove('disabled');
            completeButton.addEventListener('click', activityComplete);
            //show and center
            completeButton.style.display = 'block';
            completeButton.parentElement.style.display = 'flex';
            completeButton.parentElement.style.flexDirection = 'column';
            completeButton.parentElement.style.alignItems = 'center';
        }
        else {
            content.addEventListener('ended', activityComplete);
        }
    }
    else if (hasQuiz) {
        //do nothing - call activity complete in AJAX request
        console.log('Type: quiz');
        // getQuiz();
    }
    else if (hasJournal) {
        console.log('Type: journal');
    }
    else {
        //if no content - complete activity
        activityComplete();
    }

    //COMPLETION
    function activityComplete(message=true) {
        //show content
        console.log('activity completed');
        //show message
        if (message) {
            const hasMessage = {{ isset($activity->completion_message) ? 'true' : 'false' }};
            if (hasMessage) {
                const completionMessageDiv = document.getElementById('comp_message');
                completionMessageDiv.style.display = 'block';
            }
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
                //unlock redirect only after progress is processed
                if (response.data.unlocked_bonus) {
                    const bonusMessageDiv = document.getElementById('bonus_message');
                    bonusMessageDiv.style.display = 'block';
                }
                //hiding complete button for images
                if (type == 'image') {
                    const completeButton = document.getElementById('img_complete_activity');
                    completeButton.classList.add('disabled');
                    completeButton.style.display = 'none';
                }
                unlockRedirect();
            })
            .catch(error => {
                console.error('There was an error updating the progress:', error);
            });
        }
        else if (status == 'completed') {
            unlockRedirect();
        }
    }

    //function for unlocking the redirection buttons
    function unlockRedirect() {
        redirectDiv.querySelectorAll('.redirect-btn').forEach(btn => {
            btn.style.display = 'block';
            btn.classList.remove('disabled');
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


    //VOICE SELECTION
    function selectVoice(voice) {
        //change drop down text and hidden value
        document.getElementById('voice_dropdown_button').innerHTML = voice;
        document.getElementById('voice_select').value = voice;

        //make sure the correct dropdown shows
        document.querySelectorAll('.time-dropdown').forEach(dropdown => {
            if (dropdown.getAttribute('voice') === voice) {
                //display if there are options
                dropdown.style.display = dropdown.getAttribute('data-display');
                //get the time value
                var time = dropdown.querySelector('.time-toggle').getAttribute('time');
                //set the new time
                selectTime(time);
            } else {
                dropdown.style.display = 'none';
            }
        });
    }
    //TIME SELECTION
    function selectTime(time) {
        //get the voice
        var voice = document.getElementById('voice_select').value;
        //change the correct drop down
        document.getElementById('time_dropdown_button_'+voice).innerHTML = time+' min';
        //change hidden value
        document.getElementById('time_select').value = time;
        //change the content
        handleVoiceTimeChange();
    }

    function handleVoiceTimeChange() {
        //get the inputs
        var voice_input = document.getElementById('voice_select').value;
        var time_input = document.getElementById('time_select').value;

        //update which content is displayed, pause others, change eventListener
        document.querySelectorAll('.content-main').forEach(content => {
            if (content.getAttribute('voice') === voice_input && content.getAttribute('time') === time_input) {
                content.style.display = 'block';
                content.querySelector('audio').addEventListener('ended', activityComplete);
            } else {
                content.style.display = 'none';
                content.querySelector('audio').pause();
                content.querySelector('audio').removeEventListener('ended', activityComplete);
            }
        });
    }

    //ON CONTENT LOAD
    document.addEventListener('DOMContentLoaded', function() {
        if (hasContent) {
            const audioOptions = @json($content ? $content->audio_options : null);
            if (audioOptions) {
                console.log('Audio options loaded:', audioOptions);
                const voice_select = document.getElementById('voice_select');
                const time_select = document.getElementById('time_select');
                if (voice_select && time_select) {
                    //first call to set up options
                    selectVoice(voice_select.value);
                    document.getElementById('audio-options-div').style.display = 'block';
                }
            } else {
                //behave as normal - event listeners are on all audio items
                console.log('No audio options available.');
            }
        }
        //NOSEEK
        var mediaPlayers = document.querySelectorAll('.slide__audio-player');
        mediaPlayers.forEach(function(player) {
            var timeTracking = {
                watchedTime: 0,
                currentTime: 0
            };
            var lastUpdated = 'currentTime';
            var isSeeking = false;
            var endedListener;
            var MAX_DELTA = 1;
            //allows seek by setting watched time to the duration
            if (allowSeek) {
                timeTracking.watchedTime = player.duration;
            }

            player.addEventListener('timeupdate', function () {
                //block seeking timeupdate
                if (!isSeeking && !player.seeking || allowSeek) {
                    //tracking watched time - only update if the time is less than 1 second - prevent seek spam bug
                    var delta = player.currentTime - timeTracking.watchedTime;
                    if (delta <= MAX_DELTA && delta >= 0) {
                        timeTracking.watchedTime = player.currentTime;
                        lastUpdated = 'watchedTime';
                    }
                    //tracking the current time (if less than watched)
                    else {
                        timeTracking.currentTime = player.currentTime;
                        lastUpdated = 'currentTime';
                    }
                }
            });

            //prevent seek
            player.addEventListener('seeking', function () {
                isSeeking = true;
                //block seeking if seek puts current ahead of watchedTime
                //allows rewind and ability to catch up
                console.log('Seeking');
                var delta = player.currentTime - timeTracking.watchedTime;
                if (delta > 0) {
                    //temp remove ended listener - seeking spam bug
                    if (endedListener) {
                        console.log('removing listener')
                        player.removeEventListener('ended', endedListener);
                    }

                    //pause play back from last time
                    player.pause();
                    player.currentTime = timeTracking[lastUpdated];
                    player.play();
                }
            });

            player.addEventListener('seeked', function () {
                isSeeking = false;
            });

            //init event listener
            endedListener = function() {
                console.log('Media ended');
                if (timeTracking.watchedTime < player.duration) {
                    console.log('Blocked seek spam');
                }
                activityComplete();
            };
            console.log('adding end listener');
            player.addEventListener('ended', endedListener);
        });
    });

    //SHOW ERRORS
    const errorDiv = document.getElementById('error-messages');
    function showError(errorMessage) {
        errorDiv.textContent = errorMessage;
        errorDiv.style.display = 'block';
    }
</script>
@endsection

