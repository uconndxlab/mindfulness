@extends('layouts.main')

@section('title', $activity.': Quiz')

@section('content')
<div class="col-md-8">
    <div class="text-left">
        @php
            $quizOptions = $quiz->options_feedback ?? [];
        @endphp

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display font-weight-bold">Quiz</h1>
            </div>
            <div>
                <h1 class="display fw-bold">
                    <a id="exit_btn" class="btn btn-link" href="{{ route('explore.home') }}">
                        <i id="exit_icon" class="bi bi-x-lg"></i>
                    </a>
                </h1>
            </div>
        </div>
        <h2>{{ $quiz->question }}</h2>
    </div>
    <form action="{{ route('quiz.submit', $quiz->id) }}" method="POST" class="manual-margin-top">
        @csrf
        @foreach ($quizOptions as $index => $optionFeedback)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="answer" id="option_{{ $index }}" value="{{ $index }}" {{ old('answer') == $index ? 'checked' : '' }}>
                <label class="form-check-label" for="option_{{ $index }}">
                    {{ $optionFeedback['option'] }}
                </label>
            </div>
        @endforeach

        @if(session('feedback'))
            <div class="mt-3 {{ session('is_correct') ? 'text-success' : 'text-danger' }}">
                {{ session('feedback') }}
            </div>
        @endif

        <button type="submit" id="submitButton" class="btn btn-primary mt-3">SUBMIT</button>
            
    </form>
    
    @if (session('is_correct'))
        <div class="container manual-margin-top">
            <a id="nextButton" class="btn btn-success" href="{{ route('explore.lesson', ['lessonId' => $next]) }}">NEXT</a>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const submitButton = document.getElementById('submitButton');
        const radioButtons = document.querySelectorAll('input[name="answer"]');

        //check if any radio buttons are checked
        function checkRadioButtons() {
            let isAnyChecked = false;
            radioButtons.forEach(radio => {
                if (radio.checked) {
                    isAnyChecked = true;
                }
            });
            submitButton.disabled = !isAnyChecked;
        }
        //initially disabled
        submitButton.disabled = true;

        //eventlistenters on radio buttons
        radioButtons.forEach(radio => {
            radio.addEventListener('change', checkRadioButtons);
        });

        //check initial selection
        checkRadioButtons();
    });
</script>
@endsection
