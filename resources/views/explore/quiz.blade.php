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
    <form action="{{ route('quiz.submit', $quiz->id) }}" method="POST" class="pt-3">
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
        
        @if (session('is_correct'))
            <div class=" manual-margin-top">
                <a id="nextButton" class="btn btn-success" href="{{ route('explore.lesson', ['lessonId' => $next]) }}">NEXT <i class="bi bi-arrow-right"></i></a>
            </div>
        
        @else
            <div class=" manual-margin-top">
                <button type="submit" id="submitButton" class="btn btn-secondary mt-4">SUBMIT</button>
            </div>
        @endif
    </form>
    
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
