@extends('layouts.main')

@section('title', 'Journal')

@section('content')
<div class="col-md-8">
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
        <div class="form-group dropdown">
            <label class="fw-bold col-12" for="word_dropdown">Word of the day:</label>
            <button id="word-of-day" class="btn btn-xlight dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Select a word
            </button>
            <ul class="dropdown-menu @error('word_otd') is-invalid @enderror" id="word_dropdown" name="word_dropdown">
                <li><button class="dropdown-item" type="button" value="relax" onclick="showWord(this)">Relax</button></li>
                <li><button class="dropdown-item" type="button" value="compassion" onclick="showWord(this)">Compassion</button></li>
                <li><button class="dropdown-item" type="button" value="other" onclick="showWord(this)">More options...</button></li>
            </ul>
            <input type="hidden" name="word_otd" id="word_otd" value="">
            @error('word_otd')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="form-group mt-3 ">
            <label class="fw-bold" for="note">New Note:</label>
            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="5">{{ old('note') }}</textarea>
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
    <div id="past_notes">
        @if (isset($notes))
            <h3 class="fw-bold mt-3">Previous Notes:</h3>
            @foreach ($notes as $index => $note)
                <div class="prior-note">
                    <div class="top-note">
                        <h5 class="fw-bold d-flex justify-content-between">
                            <span>{{ $note->word_otd }}</span>
                        </h5>
                        <small>{{ $note->formatted_date }}</small>
                    </div>
                    @if (strlen($note->note) > 100)
                        <div id="note_content_{{ $index }}" class="note-content-extra">
                            <p class="note-content">
                                {{ substr($note->note, 0, 75) }}
                                <span class="dots">
                                    ...
                                </span>
                                <span class="more-text" style="display: none">
                                    {{ substr($note->note, 75) }}
                                </span>
                            </p>
                            <button id="read_more_{{ $index }}"class="btn btn-link read-more-btn">Read More...</button>
                        </div>
                    @else
                        <p id="note_content_{{ $index }}" class="note-content">
                            {{ $note->note }}
                        </p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
    <div>
        {{ $notes->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
<script>
    function showWord(item) {
        document.getElementById("word-of-day").innerHTML = item.innerHTML;
        document.getElementById("word_otd").value = item.value;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const notesDiv = document.getElementById('past_notes');
        //get all notes with extra
        document.querySelectorAll('.note-content-extra').forEach(readMoreDiv => {
            //get the button and text
            var readMoreBtn = readMoreDiv.querySelector('.read-more-btn');
            var dots = readMoreDiv.querySelector('.dots');
            var moreText = readMoreDiv.querySelector('.more-text');
            readMoreBtn.addEventListener('click', function() {
                if (moreText.style.display === 'none') {
                    moreText.style.display = 'inline';
                    dots.style.display = 'none';
                    readMoreBtn.textContent = 'Read Less';
                } else {
                    moreText.style.display = 'none';
                    dots.style.display = 'inline';
                    readMoreBtn.textContent = 'Read More...';
                }
            });
        });
    });
</script>
@endsection
