@extends('layouts.main')

@section('title', 'Journal')

@section('content')
<div class="d-flex align-items-center justify-content-center" style="min-height: 100vh;"></div>
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
                    <option value="Relax">Relax</option>
                    <option value="Compassion">Compassion</option>
                    <option value="Other">More options...</option>
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
    </div>
</div>


<!-- <div class="container mt-4">
    <h2>Past Notes:</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="past_notes">Select a Note:</label>
                <select class="form-control" id="past_notes">
                    <option value="" selected disabled>Select a note...</option>
                    @foreach($notesList as $note)
                    <option value="{{ $note->id }}">{{$note->note}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div id="note_content"></div>
        </div>
    </div>
</div> -->
@endsection