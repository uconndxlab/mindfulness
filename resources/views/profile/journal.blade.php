@extends('layouts.main')

@section('title', 'Journal')

@section('content')
<div class="col-8">
    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif
    <div class="text-left">
        <h1 class="display fw-bold">Journal</h1>
    </div>
    <form method="POST" action="{{ route('note.store') }}">
        @csrf
        <div class="form-group">
            <label class="fw-bold" for="word_otd">Word of the day:</label>
            <select class="form-control" id="word_otd" name="word_otd">
                <option value="relax">Relax</option>
                <option value="compassion">Compassion</option>
                <option value="other">More options...</option>
            </select>
        </div>

        <div class="form-group mt-3">
            <label class="fw-bold" for="note">New Note:</label>
            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="5" value="{{ old('note') }}"></textarea>
            @error('note')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="text-center mt-3">
            <div class="form-group">
                <button type="submit" class="btn btn-success">SAVE NOTE</button>
            </div>
        </div>
    </form>
    <h3 class="fw-bold">Previous Notes:</h3>
    @foreach ($notes as $note)
        <h5 class="fw-bold d-flex justify-content-between">
            <span>{{ $note->word_otd }}</span>
            <span>{{ $note->formatted_date }}</span>
        </h5>
        <p class="note-content">{{ $note->note }}</p>
    @endforeach
</div>
@endsection
