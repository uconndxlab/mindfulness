@extends('layouts.app')

@section('title', $page_info['title'])

@section('content')
<div class="col-md-8">
    @php
        $route_name = Request::route()->getName();
        $top_nav = [false, false];
        if (isset($page_info['journal']) && $page_info['journal']) {
            $tn_right_name = 'History';
            $tn_right_route = route('journal.library');
            $tn_left_name = 'Write';
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
    <div class="text-left">
        <h1 class="display fw-bold">Journal</h1>
    </div>

    <div id="journalContainer">
        <x-journal :journal="$journal"/>
    </div>
</div>
@endsection
