@extends('layouts.main')

@section('title', $page_info['title'])

@section('content')
<div class="col-md-8">
    @php
        $route_name = Request::route()->getName();
        $top_nav = [false, false];
        if (isset($page_info['journal']) && $page_info['journal']) {
            $tn_right_name = 'History';
            $tn_right_route = route('journal.library');
            $tn_left_name = 'Compose';
            $tn_left_route = route('journal.compose');
            if ($route_name == 'journal.compose') {
                $top_nav[0] = true;
            }
            else {
                $top_nav[1] = true;
            }
        }
        else {
            $tn_right_name = 'Favorites';
            $tn_right_route = route('library.favorites');
            $tn_left_name = 'Meditation';
            $tn_left_route = route('library.meditation');
            if ($route_name == 'library.meditation') {
                $top_nav[0] = true;
            }
            else {
                $top_nav[1] = true;
            }
        }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="tabs">
            <ul class="navbar-nav" style="flex-direction:row">
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link {{ $top_nav[0] ? 'active disabled' : ''}}" href="{{ $top_nav[0] ? '' : $tn_left_route }}">{{ $tn_left_name }}</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link {{ $top_nav[1] ? 'active disabled' : ''}}" href="{{ $top_nav[1] ? '' : $tn_right_route }}">{{ $tn_right_name }}</a>
                </li>
            </ul>
        </div>
    </nav>

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
