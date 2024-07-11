@extends('layouts.main')

@section('title', $quiz->activity->title.': Quiz')

@section('content')
<div class="col-md-8">
    <div class="text-left">
        @php
            $quiz_options = $quiz->options_feedback ?? [];
        @endphp

        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="display font-weight-bold">Quiz</h1>
            </div>
            <div>
                <h1 class="display fw-bold">
                    <a id="exit_btn" class="btn btn-link" href="{{ $exit_route }}">
                        <i id="exit_icon" class="bi bi-x-lg"></i>
                    </a>
                </h1>
            </div>
        </div>
        <h2>{{ $quiz->question }}</h2>
    </div>
    <form action="{{ route('quiz.submit', ['quiz_id' => $quiz->id]) }}" method="POST" class="pt-3" id="quizForm">
        @csrf
        @foreach ($quiz_options as $index => $option_feedback)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="answer" id="option_{{ $index }}" value="{{ $index }}" {{ old('answer') == $index ? 'checked' : '' }} {{ session('is_correct') ? 'disabled' : ''}}>
                <label class="form-check-label" for="option_{{ $index }}">
                    {{ $option_feedback['option'] }}
                </label>
            </div>
        @endforeach

        @if(session('feedback'))
            <div class="mt-3 {{ session('is_correct') ? 'text-success' : 'text-danger' }}">
                {{ session('feedback') }}
            </div>
        @endif

        <div class=" manual-margin-top">
            <a id="nextButton" class="btn btn-success" href="{{ $redirect_route }}" style="display: {{ session('is_correct') ? 'block' : 'none'}};">
                {{ $redirect_label }}
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class=" manual-margin-top">
            <button type="submit" id="submitButton" class="btn btn-secondary mt-4" style="display: {{ session('is_correct') ? 'none' : 'block'}};">SUBMIT</button>
        </div>
    </form>
    
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const savedAnswer = {{ $saved_answer ? $saved_answer['answer'] : null }};
        const submitButton = document.getElementById('submitButton');
        const radioButtons = document.querySelectorAll('input[name="answer"]');
        // const submissionFlag = 'formSubmitted';

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

        if (savedAnswer != null) {
            var selectedOption = document.getElementById('option_' + savedAnswer);
            if (selectedOption) {
                selectedOption.checked = true;
            }
            // localStorage.setItem(submissionFlag, 'true');

            //submitting led to infinite loop
            //make request
        //     return new Promise((resolve, reject) => {
        //     axios.post('}} route('quiz.submit') }}', {
        //         quiz_id: quiz_id,
        //         resubmit : true
        //     }, {
        //         headers: {
        //             'X-CSRF-TOKEN': '}} csrf_token() }}'
        //         }
        //     })
        //     .then(response => {
        //         console.log(response.data.message);
        //         resolve(true);
        //     })
        //     .catch(error => {
        //         console.error('There was an error submitting the quiz', error);
        //         reject(false);
        //     });
        // });
        }

        //check initial selection
        checkRadioButtons();
    });
</script>
@endsection
