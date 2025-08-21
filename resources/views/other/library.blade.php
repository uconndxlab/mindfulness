@extends('layouts.app')

@section('title', $page_info['title'])

@section('content')
<div class="col-lg-8" id="library-root" data-library-search-route="{{ $page_info['search_route'] }}" data-library-base-param="{{ $base_param }}" data-library-favorites="{{ isset($is_favorites) && $is_favorites ? 'true' : 'false' }}" data-library-wipe-filters="{{ isset($wipe_filters) && $wipe_filters ? 'true' : 'false' }}">
    @php
        use Illuminate\Support\Facades\Request;
        use Illuminate\Support\Str;

        $route_name = Request::route()->getName();
        $top_nav = [false, false];
        if (isset($page_info['journal']) && $page_info['journal']) {
            $journal_hide = true;
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
            $journal_hide = false;
            $tn_right_name = 'Search';
            $tn_right_route = route('library.main');
            $tn_left_name = 'Favorites';
            $tn_left_route = route('library.favorites');
            if ($route_name == 'library.favorites') {
                $top_nav[0] = true;
                $hide_search = true;
            }
            else {
                $top_nav[1] = true;
            }
        }
        $is_favorites = $route_name == 'library.favorites';
    @endphp

    <nav class="navbar navbar-expand navbar-light top-nav">
        <div class="tabs">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link {{ $top_nav[0] ? 'active disabled' : ''}}" href="{{ $top_nav[0] ? '' : $tn_left_route }}">{{ $tn_left_name }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $top_nav[1] ? 'active disabled' : ''}}" href="{{ $top_nav[1] ? '' : $tn_right_route }}">{{ $tn_right_name }}</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="text-left">
        <h1 class="display fw-bold mt-2">{{ $page_info['title'] }}</h1>
    </div>
    <div class="">
        <form id="search_filter_form" method="GET">
            <div class="row {{ isset($hide_search) && $hide_search ? 'd-none' : '' }}">
                <div class="col-lg-8">
                    <div class="input-group mb-3">
                        <i id="search-icon" class="bi bi-search pe-2"></i>
                        <input id="search_bar" type="text" name="search" id="search" class="form-control" placeholder='{{ $page_info['search_text'] }}'>
                        <span class="input-group-text">
                            <a id="clear_search_button" type="button" class="d-none">CLEAR</a>
                        </span>
                    </div>
                </div>
            </div>
        
            <div class="row">
                <div class="col-12">
                    <hr class="separator-line">
                </div>
            </div>

            <div>
            <div id="search_page_throbber" class="d-block position-absolute top-50 start-50 translate-middle-x z-1000">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        
            <div id="filterResultDiv" class="d-none">
                <div class="row search-filters">
                    @if (isset($categories) && !$is_favorites)
                        <div class="col-lg-4">
                            <div class="accordion accordion-flush" id="showFilterAccordion">
                                <div class="form-group accordion-item border mb-2">
                                    <h2 class="accordion-header" id="headingFilter">
                                        <button id="showFilterButton" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="true" aria-controls="collapseFilter">
                                            <i class="bi bi-sliders pr-2"></i><span id="showFilterText"> Show Filters</span>
                                        </button>
                                    </h2>
                                    <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter">
                                        <div class="accordion-body">
                                            <div class="accordion accordion-flush mb-3" id="filter_accordion">

                                                <div class="form-group accordion-item border mb-2 {{ $journal_hide ? 'd-none' : '' }}">
                                                    <h2 class="accordion-header" id="headingTime">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTime" aria-expanded="true" aria-controls="collapseTime">
                                                            Time
                                                        </button>
                                                    </h2>
                                                    <div id="collapseTime" class="accordion-collapse collapse" aria-labelledby="headingTime">
                                                        <div class="accordion-body">
                                                            <div class="position-relative">
                                                                <div id="slider_value_bubble" class="d-none slider-bubble">0 min</div>
                                                                <div id="time_range_slider"></div>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span id="start_time_label">0 min</span>
                                                                <span id="end_time_label">30 min</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" name="start_time" id="start_time_input">
                                                    <input type="hidden" name="end_time" id="end_time_input">
                                                </div>
                        
                                                @php
                                                    $journal_search = isset($page_info['journal']) && $page_info['journal'] ? true : false;
                                                @endphp
                                                <div class="form-group accordion-item border mb-2">
                                                    <h2 class="accordion-header" id="headingCategory">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategory" aria-expanded="true" aria-controls="collapseCategory">
                                                            @if ($journal_search)
                                                                Topics
                                                            @else
                                                                Category
                                                            @endif
                                                        </button>
                                                    </h2>
                                                    <div id="collapseCategory" class="accordion-collapse collapse" aria-labelledby="headingCategory">
                                                        <div class="accordion-body">
                                                            <div id="category_check">
                                                                @foreach ($categories as $category)
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="category[]" id="category_{{ strtolower($category) }}" value="{{ $category }}">
                                                                        <label class="form-check-label" for="category_{{ Str::slug($category) }}">
                                                                            {{ $category }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                                @if ($journal_search)
                                                                    <div class="text-left fw-bold mt-1">Other:</div>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="category[]" id="category_activities" value="Activities">
                                                                        <label class="form-check-label" for="category_activities">
                                                                            From Activity
                                                                        </label>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                
                                                <div class="form-group accordion-item border mb-2 {{ $journal_hide ? 'd-none' : '' }}">
                                                    <h2 class="accordion-header" id="headingModule">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModule" aria-expanded="true" aria-controls="collapseModule">
                                                            Part
                                                        </button>
                                                    </h2>
                                                    <div id="collapseModule" class="accordion-collapse collapse" aria-labelledby="headingModule">
                                                        <div class="accordion-body">
                                                            <div id="module_check">
                                                                @for ($i = 1; $i < 5; $i++)
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox" name="module[]" id="module_{{ $i }}" value="{{ $i }}" {{ in_array($i, request('module', [])) ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="module_{{ $i }}">
                                                                            Part {{ $i }}
                                                                        </label>
                                                                    </div>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button id="apply_filter_button" type="button" class="btn btn-primary">Apply Filter</button>
                                            <button id="clear_filter_button" type="button" class="btn btn-link text-center mt-1 mb-2 text-dark">Clear Filters</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-lg-8 mx-auto position-relative">
                        <div id="throbber" class="d-none position-absolute top-50 start-50 translate-middle-x z-1000">
                            <div class="spinner-border text-secondary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="resultsContainer"></div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
