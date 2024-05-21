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
            $end_behavior = $lesson->end_behavior;
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

        
        <h3 class="font-weight-bold">Quiz Details: ***If applicable</h3>
        <div class="form-group">
            <label for="quiz_question" class="font-weight-bold">Question:</label>
            <input type="text" class="form-control" id="quiz_question" name="quiz_question" value="{{ old('quiz_question') }}">
        </div>

        @for ($i = 1; $i <= 5; $i++)
            <div class="form-group">
                <label for="option_{{ $i }}" class="font-weight-bold">Option {{ $i }}:</label>
                <input type="text" class="form-control" id="option_{{ $i }}" name="option_{{ $i }}" value="{{ old('option_'.$i) }}">
            </div>

            <div class="form-group">
                <label for="feedback_{{ $i }}" class="font-weight-bold">Feedback {{ $i }}:</label>
                <textarea type="text" class="form-control" id="feedback_{{ $i }}" name="feedback_{{ $i }}">{{ old('feedback_'.$i) }}</textarea>
            </div>
        @endfor

        <div class="form-group">
            <label for="quiz_correct_answer" class="font-weight-bold">Correct Answer (number):</label>
            <input type="number" class="form-control" id="quiz_correct_answer" name="quiz_correct_answer" value="{{ old('quiz_correct_answer') }}">
        </div>

        <div class="text-center">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">SAVE</button>
            </div>
        </div>
    </form>

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
@endsection
