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
@endsection