@extends('layouts.main')

@section('title', 'Journal')

@section('content')
<div class="col-md-6">
    @if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
    @endif
    <form method="POST" action="{{ route('note.store') }}">
        @csrf
        <div class="form-group"">
            <label class="font-weight-bold" for="word_otd">Word of the day:</label>
            <select class="form-control" id="word_otd" name="word_otd">
                <option value="relax">Relax</option>
                <option value="compassion">Compassion</option>
                <option value="other">More options...</option>
            </select>
        </div>

        <div class="form-group">
            <label class="font-weight-bold" for="note">New Note:</label>
            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="5" value="{{ old('note') }}"></textarea>
            @error('note')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="text-center">
            <div class="form-group">
                <button type="submit" class="btn btn-success">SAVE NOTE</button>
            </div>
        </div>
    </form>
    <h3 class="font-weight-bold">Previous Notes:</h3>
    @foreach ($notes as $note)
        <h5 class="font-weight-bold d-flex justify-content-between">
            <span>{{ $note->word_otd }}</span>
            <span>{{ $note->formatted_date }}</span>
        </h5>
        <p class="note-content">{{ $note->note }}</p>
    @endforeach
</div>
@endsection


<!-- <div class="accordion" id="accordionExample">
        @foreach ($notes as $note)
        <div class="accordion-item">
            <h2 class="accordion-header" id="{{ 'heading'.$note->id }}">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="{{ '#collapse'.$note->id }}" aria-expanded="true" aria-controls="{{ 'collapse'.$note->id }}">
                    {{ $note->word_otd }} created at: {{ $note->created_at }}
                </button>
            </h2>
            <div id="{{ 'collapse'.$note->id }}" class="accordion-collapse collapse" aria-labelledby="{{ 'heading'.$note->id }}" data-bs-parent="#accordionExample">
                <div class="accordion-body">
                    {{ $note->note }}
                </div>
            </div>
        </div>
        @endforeach
    </div> -->