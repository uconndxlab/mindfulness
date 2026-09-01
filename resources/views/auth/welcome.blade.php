@extends('layouts.auth')

@section('title', 'Welcome!')
@section('page_id', 'auth-welcome')

@section('content')
<div class="col-12 welcome-wizard-col dark-background" id="welcome-wizard">
    <div class="welcome-wizard-header">
        <h1 id="welcome-title" class="display-3 fs-1 fw-bold mb-1">Welcome Letter</h1>
        <p id="welcome-desc" class="mb-0">Tap the letter and scroll it as you read.</p>
    </div>

    <div class="welcome-step-body">
        <div class="welcome-step" data-step="1">
            <div class="welcome-letter-paper welcome-letter-paper--preview" data-open-letter-modal role="button" tabindex="0" aria-label="Open welcome letter">
                @include('components.welcome-letter')
            </div>
        </div>
        <div class="welcome-step d-none" data-step="2">
            <div class="welcome-video-frame">
                <x-contentView
                    id="pi_welcome_video"
                    type="video"
                    path="{{ config('welcome.pi_video') }}"
                    :allowSeek="true"
                    controlsList="nodownload noplaybackrate"
                />
            </div>
        </div>
        <div class="welcome-step d-none" data-step="3">
            <div class="welcome-video-frame">
                <x-contentView
                    id="tutorial_video"
                    type="video"
                    path="{{ config('tutorial.video_file') }}"
                    :allowSeek="true"
                    controlsList="nodownload noplaybackrate"
                />
            </div>
        </div>
    </div>

    <div class="welcome-wizard-nav">
        <div class="welcome-wizard-controls">
            <button type="button" id="welcome-prev" class="btn-quiz invisible" disabled>
                <i class="bi bi-arrow-left"></i> Previous
            </button>
            <button type="button" id="welcome-next" class="btn-quiz">
                Next <i class="bi bi-arrow-right"></i>
            </button>
            <form method="POST" action="{{ route('welcome.complete') }}" id="welcome-complete-form" class="d-none">
                @csrf
                <button type="submit" class="btn btn-primary mb-0">Begin Your Journey</button>
            </form>
        </div>
        <div class="welcome-dots" role="tablist" aria-label="Welcome steps">
            <span class="welcome-dot active" data-dot="1" aria-current="step"></span>
            <span class="welcome-dot" data-dot="2"></span>
            <span class="welcome-dot" data-dot="3"></span>
        </div>
    </div>
</div>

@include('components.welcome-letter-modal')
@endsection
