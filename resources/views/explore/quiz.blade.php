@extends('layouts.main')

@section('title', $activityTitle.': Quiz')

@section('content')
<div class="col-md-8">
    <div class="text-left">
        @php
            $quizOptions = json_decode($quiz->options_feedback) ?? [];
        @endphp

        <h1 class="display font-weight-bold">Quiz</h1>
        <h2>{{ $quiz->question }}</h2>
    </div>
    <form action="{{ route('quiz.submit', $quiz->id) }}" method="POST" class="manual-margin-top">
        @csrf
        @foreach ($quizOptions as $index => $optionFeedback)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="answer" id="option_{{ $index }}" value="{{ $index }}" {{ old('answer') == $index ? 'checked' : '' }}>
                <label class="form-check-label" for="option_{{ $index }}">
                    {{ $optionFeedback->option }}
                </label>
            </div>
        @endforeach

        @if(session('feedback'))
            <div class="mt-3 {{ session('is_correct') ? 'text-success' : 'text-danger' }}">
                {{ session('feedback') }}
            </div>
        @endif

        <button type="submit" id="submitButton" class="btn btn-primary mt-3">Submit</button>
            
    </form>
    
    @if (session('is_correct'))
        <div class="text-left">
            <div class="container manual-margin-top">
                <a id="redirectButton" class="btn btn-success" href="{{ route('explore.home') }}">EXIT QUIZ</a>
            </div>
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
