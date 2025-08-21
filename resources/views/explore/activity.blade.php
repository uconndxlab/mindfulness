@extends('layouts.app')

@section('title', $activity->title)

@section('content')
<div class="col-md-8">
    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <div id="error-messages" class="alert alert-danger d-none"></div>
    <div class="text-left">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="display fw-bold">{{ $activity->title }}
                <button id="favorite_btn" class="btn btn-link">
                    <i id="favorite_icon" class="bi bi-star"></i>
                </button>
            </h1>
        </div>
        @if ($activity->type)
            <span class="sub-activity-font activity-tag-{{ $activity->type }}">{{ ucfirst($activity->type) }}</span>
        @endif
        @if ($activity->time)
            <span class="sub-activity-font activity-tag-time"></i>{{ $activity->time.' min' }}</span>
        @endif
    </div>
    <div class="manual-margin-top">
        <!-- audio -->
        @if (($activity->type == 'practice' || $activity->type == 'lesson') && $content)
            @if (isset($content->instructions))
                <div class="text-left mb-3">
                    <h5>@markdown($content->instructions)</h5>
                </div>
            @endif
            @php
                $allowSeek = $activity->completed;
                $allowPlaybackRate = $activity->type == 'practice' && $activity->completed;

                $hasAudioOptions = isset($content->audio_options) && !empty($content->audio_options);
                
                if ($hasAudioOptions) {
                    $defaultVoice = key($content->audio_options);
                    $multipleVoices = count($content->audio_options) > 1;
                }
            @endphp
            
            <!-- voice selection dropdown -->
            @if ($hasAudioOptions)
                <x-voice-selector :voices="$content->audio_options" :defaultVoice="$defaultVoice" :showDropdown="$multipleVoices"/>

                <!-- audio content views -->
                <div class="mt-4">
                    @foreach ($content->audio_options as $voice => $file_path)
                        <div id="audio_content" class="content-main d-none" voice="{{ $voice }}" data-type="audio">
                            <x-audio-player :file="$file_path" :id="$voice" :allowSeek="$allowSeek" :allowPlaybackRate="$allowPlaybackRate"/>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- default video, image -->
                <div id="content_main" class="content-main d-flex justify-content-center align-items-center flex-column" data-type="{{ $content->type }}">
                    <x-contentView id="content_view" id2="download_btn" voiceId="none" type="{{ $content->type }}" file="{{ $content->file_path }}" allowSeek="{{ $allowSeek }}"/>
                </div>
            @endif

        <!-- other content types -->
        @elseif ($activity->type == 'reflection' && $quiz)
            <div id="quizContainer">
                <x-quiz :quiz="$quiz"/>
            </div>
        @elseif ($activity->type == 'journal' && $journal)
            <div id="journalContainer">
                <x-journal :journal="$journal"/>
            </div>
        @endif
        <div id="comp_message" class="mt-2 d-none">
            <div class="text-success">@markdown(is_string($activity->completion_message ?? null) ? $activity->completion_message : 'Congrats on completing this activity!')</div>
        </div>
    </div>
    <div class="manual-margin-top" id="redirect_div">
        @if (isset($page_info['redirect_route']))
            <a id="redirect_button" class="btn btn-primary btn-tertiary redirect-btn disabled d-none" href="{{ $page_info['redirect_route'] }}">
                {{ $page_info['redirect_label'] }}
            </a>
        @endif
        @php
            $comp_late_btn_disp = !$activity->skippable ? 'none' : 'block';
        @endphp
        <div class="d-flex justify-content-center">
            <button id="complete-later" class="btn btn-outline-primary rounded-pill px-4 {{ !$activity->skippable ? 'd-none' : '' }}" type="button">
                <i class="bi bi-bookmark me-2"></i>
                I will do this later
            </button>
        </div>
    </div>
</div>
<script>
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp

    const activity_id = {{ $activity->id }};
    const start_log_id = {{ $start_log_id }};
    const day = '{{ $activity->day->name }}';
    const optional = {{ $activity->optional ? 'true' : 'false' }};

    //COMPLETION ITEMS
    const redirectDiv = document.getElementById('redirect_div');
    const compLateBtn = document.getElementById('complete-later');
    const hasContent = {{ $content ? 'true' : 'false' }};
    const hasQuiz = {{ $quiz ? 'true' : 'false' }};
    const hasJournal = {{ $journal ? 'true' : 'false' }};
    var allowSeek = false;
    var completed = false;

    //CHECKING COMPLETION
    const status = '{{ $activity->completed ? 'completed' : 'unlocked' }}';
    if (status == 'completed') {
        allowSeek = true;
    }

    var type = null;
    //set eventlisteners to call activityComplete
    if (hasContent) {
        console.log('Type: content');
        //applies to all content items
        const content = document.getElementById('content_view');
        type = '{{ isset($content->type) ? $content->type : null }}';
        if (content) {
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
                completeButton.classList.remove('d-none');
                completeButton.parentElement.style.display = 'flex';
                completeButton.parentElement.style.flexDirection = 'column';
                completeButton.parentElement.style.alignItems = 'center';
            }
            else if (type == 'video') {
                // find video player in content main
                const videoPlayer = document.getElementById('content_view');
                if (videoPlayer) {
                    console.log('video player found');
                    videoPlayer.addEventListener('ended', activityComplete);
                }
                else {
                    console.log('video player not found');
                }
            }
        }
        else {
            // audio
            const audioContent = document.getElementById('audio_content');
            if (audioContent) {
                const audioPlayer = audioContent.querySelector('.slide__audio-player');
                audioPlayer.addEventListener('ended', activityComplete);
            }
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

    //COMPLETION
    function activityComplete(message=true) {
        //show content
        console.log('activity completed');
        completed = true;
        //update users progress
        if (status == 'unlocked' || status == 'completed') {
            axios.post('/activities/complete', {
                activity_id: activity_id,
                start_log_id: start_log_id
            }, {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => {
                const data = response.data;
                if (data.success) {
                    console.log('ProgressService: ' + data.message);
                    // redirect if set
                    if (data.redirect_url) {
                        window.location.href = data.redirect_url;
                        // stop execution
                        return;
                    }

                    // unlock redirect only after progress is processed
                    // showing modal on completed days
                    if (data.day_completed) {
                        @php
                            $markdownMessage = \Illuminate\Support\Str::markdown($activity->day->completion_message ?? 'Congrats on completing ' . $activity->day->name . '!');
                        @endphp
                        showModal({
                            label: 'Day Completed',
                            body: @json($markdownMessage),
                            media: '{{ Storage::url('flowers/'.($activity->day->media_path ? $activity->day->media_path : '')) }}',
                            route: null
                        });
                    }

                    //hiding complete button for images
                    if (type == 'image') {
                        const completeButton = document.getElementById('img_complete_activity');
                        completeButton.classList.add('disabled');
                        completeButton.classList.add('d-none');
                    }

                    // show redirect only if not already completed
                    if (status == 'unlocked') {
                        unlockRedirect(message);
                    }
                    // show completion message regardless
                    showCompletionMessage();
                }
            })
            .catch(error => {
                console.error('There was an error updating the progress:', error);
                alert('Error: ' + error.message);
            });
        }
    }
    // ensure globally accessible for components
    window.activityComplete = activityComplete;

    //function for unlocking the redirection buttons
    function unlockRedirect() {
        redirectDiv.querySelectorAll('.redirect-btn').forEach(btn => {
            btn.classList.remove('d-none');
            btn.classList.remove('disabled');
        });
        compLateBtn.classList.add('d-none');
    }
    function showCompletionMessage() {
        const completionMessageDiv = document.getElementById('comp_message');
        if (completionMessageDiv) {
            completionMessageDiv.classList.remove('d-none');
        }
    }

    //FAVORITES
    //get favorite button, icon, isFavorited value
    const favButton = document.getElementById('favorite_btn');
    let isFavorited = {{ $activity->favorited ? 'true' : 'false' }};
    const favIcon = document.getElementById('favorite_icon');
    if (isFavorited) {
        favIcon.className = 'bi bi-star-fill';
    }

    //FAVORITE HANDLING
    function toggleFavorite() {
        // change first
        const currentState = isFavorited;
        console.log('Current state: ', currentState);
        isFavorited = !isFavorited;
        favIcon.className = isFavorited ? "bi bi-star-fill" : "bi bi-star";

        // send request
        return new Promise((resolve, reject) => {
            axios.post('{{ route('favorite.toggle') }}', {
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
                console.error('There was an error toggling favorite', error);
                // revert change
                isFavorited = currentState;
                favIcon.className = isFavorited ? "bi bi-star-fill" : "bi bi-star";
                reject(false);
            });
        });
    }
    favButton.addEventListener('click', function() {
        console.log('Toggling favorite');
        toggleFavorite();
    });
    
    //ON CONTENT LOAD
    document.addEventListener('DOMContentLoaded', function() {
        //LOGGING OF PAGE INTERACTIONS
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
        let lastFocusTimestamp = performance.now();
        let exited = false;

        const logInteraction = (eventType, duration) => {
            if (exited) return;
            console.log('Logging interaction: ', eventType);
            const data = new FormData();
            data.append("activity_id", activity_id);
            data.append("event_type", eventType);
            data.append("_token", '{{ csrf_token() }}');

            // get duration in seconds
            if (duration) {
                data.append("duration", Math.round(duration / 1000));
            }
            if (eventType === 'exited') {
                data.append("start_log_id", start_log_id);
            }
            
            // exited events use sendBeacon
            if (eventType === 'exited') {
                exited = true;
                navigator.sendBeacon("{{ route('activities.log_interaction') }}", data);
            } else {
                // other events use axios
                axios.post("{{ route('activities.log_interaction') }}", data)
                    .catch(error => console.error(`Error logging ${eventType}:`, error));
            }
        };

        // log focus and unfocus events
        document.addEventListener("visibilitychange", () => {
            if (document.visibilityState === 'hidden') {
                const duration = performance.now() - lastFocusTimestamp;
                logInteraction('unfocused', duration);
            } else {
                lastFocusTimestamp = performance.now();
                logInteraction('refocused');
            }
        });

        // log exit - works for refresh, but need to manually log for back button and redirect due to modals
        window.addEventListener("pagehide", () => {
            console.log('Page hidden');
            const duration = performance.now() - lastFocusTimestamp;
            logInteraction('exited', duration);
        });

        //NOSEEK
        var mediaPlayers = document.querySelectorAll('.slide__audio-player, .video-player');
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
                        // console.log('Watched time updated: ', timeTracking.watchedTime);
                    }
                    //tracking the current time (if less than watched)
                    else {
                        timeTracking.currentTime = player.currentTime;
                        lastUpdated = 'currentTime';
                        // console.log('Current time updated');
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
                    player.currentTime = timeTracking.watchedTime;
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
            console.log('adding media end listener');
            player.addEventListener('ended', endedListener);
        });

        var showBrowserModal = true;
        //page unload warning - for progress
        // should call on page reload/changes not initiated by buttons (avoid double modals)
        window.addEventListener('beforeunload', function(e) {
            if (!completed && showBrowserModal) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // BACK BUTTON - LOSE PROGRESS
        const backButton = document.getElementById('backButton');
        if (backButton) {
            console.log('back button found');
            backButton.addEventListener('click', function(event) {
                event.preventDefault();
                showBrowserModal = false;
                console.log('will not show other modal');

                if (!completed) {
                    showModal({
                        label: 'Leave activity?',
                        body: 'Leaving will erase your progress on this activity. Are you sure you want to leave?',
                        route: this.href,
                        method: 'GET',
                        buttonLabel: 'Leave Activity',
                        buttonClass: 'btn-danger',
                        closeLabel: 'Stay',
                        onCancel: function() {
                            console.log('cancelled in leave')
                            showBrowserModal = true;
                        }
                    });
                } else {
                    window.location.href = this.href;
                }
            });
        }

        // COMPLETE LATER
        const compLateBtn = document.getElementById('complete-later');
        if (compLateBtn) {
            compLateBtn.addEventListener('click', function(event) {
                event.preventDefault();
                console.log('Complete later');
                showBrowserModal = false;
                showModal({
                    label: 'Complete Activity Later?',
                    body: 'Click \'Continue\' to move on to the next activity. All progress on this activity will be lost. This activity must still be completed later in order to finish ' + day + '.',
                    route: '{{ route('activities.skip', ['activity_id' => $activity->id]) }}',
                    method: 'POST',
                    buttonLabel: 'Continue',
                    buttonClass: 'btn-danger',
                    onCancel: function() {
                        console.log('cancelled in complete later')
                        showBrowserModal = true;
                    }
                });
            });
        }
    });

    //SHOW ERRORS
    const errorDiv = document.getElementById('error-messages');
    function showError(errorMessage) {
        errorDiv.textContent = errorMessage;
        errorDiv.classList.remove('d-none');
    }

    // SECRET SKIP
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey && event.key.toLowerCase() === 'm') {
            event.preventDefault();
            console.log('Secret skip');
            activityComplete();
        }
    }); 
</script>
@endsection

