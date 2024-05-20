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
        }
        else {
            $header = "New Lesson:";
            $submissionRoute = route('admin.lesson.store');
            $selected = old('module');
            $title = old('title');
            $description = old('description');
        }
    @endphp

    <div class="text-left">
        <h1 class="display font-weight-bold">Content Upload:</h1>
    </div>

    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ $submissionRoute }}">
        @csrf
        
        <div class="form-group">
            <label for="module">Module</label>
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
            <label for="title">Lesson Title</label>
            <input id="title" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $title }}">
            @error('title')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" value="{{ $description }}"></textarea>
            @error('description')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="fileInput" class="form-label">Choose File</label>
            <input class="form-control @error('file') is-invalid @enderror" type="file" id="file" name="file">
            @error('title')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="text-center">
            <div class="form-group">
                <button type="submit" class="btn btn-primary">SAVE</button>
            </div>
        </div>
        </form>
</div>
@endsection
