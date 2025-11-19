@extends('layouts.app')

@section('title', $activity->title)
@section('page_id', 'activity')

@section('additional_head')
    <link rel="stylesheet" href="{{ asset('css/quiz-loading.css') }}">
@endsection

@section('content')
<div class="col-md-8" data-activity-root data-activity-id="{{ $activity->id }}" data-day-name="{{ $activity->day->name }}" data-status="{{ $activity->completed ? 'completed' : 'unlocked' }}" data-has-content="{{ $content ? 'true' : 'false' }}" data-has-quiz="{{ $quiz ? 'true' : 'false' }}" data-has-journal="{{ $journal ? 'true' : 'false' }}" data-is-favorited="{{ $activity->favorited ? 'true' : 'false' }}" data-favorite-toggle-route="{{ route('favorite.toggle') }}" data-log-interaction-route="{{ route('activities.log_interaction') }}" data-skip-route="{{ route('activities.skip', ['activity_id' => $activity->id]) }}">
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
    <div class={{ in_array($activity->type, ['reflection', 'journal']) ? 'mt-4' : 'manual-margin-top' }}>
        <!-- audio -->
        @if (($activity->type == 'practice' || $activity->type == 'lesson') && $content)
            @if (isset($content->instructions))
                <div class="text-left mb-3">
                    <div class="activity-instructions">
                        @markdown($content->instructions)
                    </div>
                </div>
            @endif
            @php
                $allowSeek = $activity->completed;
                $allowPlaybackRate = $activity->type == 'lesson' && $activity->completed;

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
                        @php
                            $audioTitle = $activity->title;
                            $audioArtist = "Healing Hearts";
                            $audioArtwork = asset('icons/hh_512x512.png');
                        @endphp
                        <div id="audio_content" class="content-main d-none" voice="{{ $voice }}" data-type="audio">
                            <x-audio-player :file="$file_path" 
                                :id="$voice" 
                                :allowSeek="$allowSeek" 
                                :allowPlaybackRate="$allowPlaybackRate"
                                :title="$audioTitle"
                                :artist="$audioArtist"
                                :artwork="$audioArtwork"/>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- default video, image -->
                <div id="content_main" class="content-main d-flex justify-content-center align-items-center flex-column" data-type="{{ $content->type }}">
                    @if ($content->type === 'image')
                        <h6 class="fw-normal text-center col-md-8 col-lg-6 col-sm-10">Take your time with this activity. Please start the timer, the activity will complete when the timer runs out.</h6>
                        <div id="timer-container" class="timer-container" data-preset-time="{{ $activity->time ?? 5 }}">
                            <div class="timer-layout">
                                <div id="timer-controls" class="timer-controls">
                                    <button id="timer-reset" class="btn btn-secondary">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                    <button id="timer-play-pause" class="btn btn-primary">
                                        <i class="bi bi-play"></i>
                                    </button>
                                </div>
                                <div id="timer-display" class="timer-display">{{ $activity->time ?? 5 }}:00</div>
                            </div>
                        </div>
                    @endif
                    <div class="mt-3">
                        <x-contentView id="content_view" id2="download_btn" voiceId="none" type="{{ $content->type }}" file="{{ $content->file_path }}" allowSeek="{{ $allowSeek }}"/>
                    </div>
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
            <div class="text-success completion-message">
                @markdown(is_string($activity->completion_message ?? null) ? $activity->completion_message : 'Congrats on completing this activity!')
            </div>
        </div>
    </div>
    <div class="mt-1" id="redirect_div">
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
@endsection

