@extends('layouts.main')

@section('title', 'Content Upload')

@section('content')
<div class="col-md-6">
    @php
        if (isset($lesson)) {
            $header = "Editing ".$lesson->title.":";
            $submissionRoute = route('admin.lesson.update', ['lessonId' => $lesson->id]);
            $selected = old('module', $lesson->module_id);
            $title = old('title', $lesson->title);
            $description = old('description', $lesson->description);
            $sub_header = old('sub_header', $lesson->sub_header);
            $file_name = "Current file: ".$lesson->file_name;
            $end_behavior = old('end_behavior', $lesson->end_behavior);
            $method = "PUT";
        }
        else {
            $header = "New Lesson:";
            $submissionRoute = route('admin.lesson.store');
            $selected = old('module', $moduleId);
            $title = old('title');
            $description = old('description');
            $sub_header = old('sub_header');
            $file_name = "Choose file:";
            $end_behavior = 'none';
            $method = "POST";
        }

        if (isset($quiz)) {
            $question = old('quiz_question', $quiz->question);
            $quizOptions = json_decode($quiz->options_feedback) ?? [];
            $answer = old('quiz_correct_answer', $quiz->correct_answer);
        }
        else {
            $question = old('quiz_question');
            $quizOptions = old('quiz_options', []);
            $answer = old('quiz_correct_answer');
        }


    @endphp

    <div class="text-left">
        <h1 class="display font-weight-bold">{{ $header }}</h1>
    </div>

    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ $submissionRoute }}" enctype="multipart/form-data">
        @csrf
        @if ($method == "PUT")
            @method("PUT")
        @endif
        
        <div class="form-group">
            <label for="module" class="font-weight-bold">Module:</label>
            <select id="module" class="form-control @error('module') is-invalid @enderror" name="module">
                <option value="NULL">Select a module...</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->id }}" {{ $selected == $module->id ? 'selected' : '' }}>
                        {{ $module->name }}
                    </option>
                @endforeach
            </select>
            @error('module')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="title" class="font-weight-bold">Title:</label>
            <input id="title" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $title }}">
            @error('title')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="sub_header" class="font-weight-bold">Sub Header:</label>
            <input id="sub_header" class="form-control @error('sub_header') is-invalid @enderror" name="sub_header" value="{{ $sub_header }}">
            @error('sub_header')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description" class="font-weight-bold">Description:</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5">{{ $description }}</textarea>
            @error('description')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="file" class="font-weight-bold">{{ $file_name }}</label>
            <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file">
            @error('file')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="end_behavior" class="font-weight-bold">End Behavior:</label>
            <select id="end_behavior" class="form-control @error('end_behavior') is-invalid @enderror" name="end_behavior">
                <option value="none" {{ $end_behavior == "none" ? 'selected' : '' }}>None</option>
                <option value="quiz" {{ $end_behavior == "quiz" ? 'selected' : '' }}>Quiz</option>
                <option value="journal" {{ $end_behavior == "journal" ? 'selected' : '' }}>Journal</option>
            </select>
            @error('end_behavior')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div id="quiz_form" style="display: none;">
            <h3 class="font-weight-bold">Quiz Details:</h3>
            <div class="form-group">
                <label for="quiz_question" class="font-weight-bold">Question:</label>
                <input type="text" class="form-control" id="quiz_question" name="quiz_question" value="{{ $question }}" required>
            </div>
            
            <div id="quiz-options">
                @php
                    $index = 1;
                @endphp
                @foreach ($quizOptions as $option)
                    <div class="form-group quiz-option" id="quiz-option-{{ $index }}">
                        <label for="option_{{ $index }}" class="font-weight-bold">Option {{ $index }}:</label>
                        <input type="text" class="form-control" id="option_{{ $index }}" name="option_{{ $index }}" value="{{ old('option_'.$index, $option->option ?? '') }}" required>
                        <label for="feedback_{{ $index }}" class="font-weight-bold">Feedback {{ $index }}:</label>
                        <textarea class="form-control" id="feedback_{{ $index }}" name="feedback_{{ $index }}">{{ old('feedback_'.$index, $option->feedback ?? '') }}</textarea>
                    </div>
                    @php
                        $index++;
                    @endphp
                @endforeach
            </div>
                
            <button type="button" class="btn btn-primary" id="add-option">+</button>
            <button type="button" class="btn btn-danger" id="remove-option">-</button>
            
            <div class="form-group">
                <label for="quiz_correct_answer" class="font-weight-bold">Correct Answer (number):</label>
                <input type="number" class="form-control" id="quiz_correct_answer" name="quiz_correct_answer" value="{{ $answer }}" required>
            </div>
        </div>

            
        <div class="text-center">
            <div class="form-group">
                <button type="submit" class="btn btn-success">SAVE</button>
            </div>
        </div>
    </form>

    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    @if (isset($lesson))
        <form method="POST" action="{{ route('admin.lesson.delete', ['lessonId' => $lesson->id])}}">
        @csrf
        @method("DELETE")
            <div class="text-center">
                <div class="form-group">
                    <button type="submit" class="btn btn-danger">DELETE</button>
                </div>
            </div>
        </form>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var endBehaviorSelect = document.getElementById('end_behavior');
        var quizForm = document.getElementById('quiz_form');
        var quizQuestionInput = document.getElementById('quiz_question');
        var quizAnswerInput = document.getElementById('quiz_correct_answer');

        //hiding quiz form
        function toggleQuizForm() {
            if (endBehaviorSelect.value === 'quiz') {
                quizForm.style.display = 'block';
                quizQuestionInput.setAttribute('required', 'required');
                quizAnswerInput.setAttribute('required', 'required');
            } else {
                quizForm.style.display = 'none';
                quizQuestionInput.removeAttribute('required');
                quizAnswerInput.removeAttribute('required');
            }
        }
        //toggle on start
        endBehaviorSelect.addEventListener('change', toggleQuizForm);
        toggleQuizForm();


        //set option count to number of quiz options when initiated
        let optionCount = {{ count($quizOptions) }};
        console.log(optionCount);

        document.getElementById('add-option').addEventListener('click', function () {
            optionCount++;
            let quizOptionsDiv = document.getElementById('quiz-options');

            //add option div to quizOptionsDiv - includes option and feedback
            let optionDiv = document.createElement('div');
            optionDiv.classList.add('form-group', 'quiz-option');
            optionDiv.id = `quiz-option-${optionCount}`;
            optionDiv.innerHTML = `
                <label for="option_${optionCount}" class="font-weight-bold">Option ${optionCount}:</label>
                <input type="text" class="form-control" id="option_${optionCount}" name="option_${optionCount}" value="{{ old('option_${optionCount}') }}" required>
                <label for="feedback_${optionCount}" class="font-weight-bold">Feedback ${optionCount}:</label>
                <textarea class="form-control" id="feedback_${optionCount}" name="feedback_${optionCount}">{{ old('feedback_${optionCount}') }}</textarea>
            `;
            quizOptionsDiv.appendChild(optionDiv);
        });

        //remove the added divs
        document.getElementById('remove-option').addEventListener('click', function () {
            if (optionCount > 1) {
                let quizOptionsDiv = document.getElementById('quiz-options');

                //remove  last option
                let lastOptionDiv = document.getElementById(`quiz-option-${optionCount}`);
                quizOptionsDiv.removeChild(lastOptionDiv);

                optionCount--;
            }
        });
    });
</script>
@endsection
