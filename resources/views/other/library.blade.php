@extends('layouts.main')

@section('title', $page_info['title'])

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.0/nouislider.min.css" rel="stylesheet">

<div class="col-lg-8">
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
        <h1 class="display fw-bold mb-4">{{ $page_info['title'] }}</h1>
    </div>
    <div class="">
        <form id="search_filter_form" method="GET">
            <div class="row" style="display: {{ isset($hide_search) && $hide_search ? 'none' : 'block' }};">
                <div class="col-lg-8">
                    <div class="input-group mb-3">
                        <i style="padding:0px 10px" id="search-icon" class="bi bi-search"></i>
                        <input id="search_bar" type="text" name="search" id="search" class="form-control" placeholder='{{ $page_info['search_text'] }}'>
                        <span class="input-group-text">
                            <a style="color:#000!important" id="clear_search_button" type="button">CANCEL</a>
                        </span>
                    </div>
                </div>
            </div>
        
            <div class="row">
                <div class="col-12">
                    <hr class="separator-line">
                </div>
            </div>
        
            <div id="filterResultDiv" style="display: none;">
                <div class="row search-filters">
                    @if (isset($categories) && !$is_favorites)
                        <div class="col-lg-4">
                            <div class="accordion accordion-flush" id="showFilterAccordion">
                                <div class="form-group accordion-item border mb-2">
                                    <h2 class="accordion-header" id="headingFilter">
                                        <button id="showFilterButton" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFilter" aria-expanded="true" aria-controls="collapseFilter">
                                            <i class="bi bi-sliders" style="padding-right:10px"></i> Show Filters
                                        </button>
                                    </h2>
                                    <div id="collapseFilter" class="accordion-collapse collapse" aria-labelledby="headingFilter">
                                        <div class="accordion-body">
                                            <div class="accordion accordion-flush mb-3" id="filter_accordion">

                                                <div class="form-group accordion-item border mb-2" style="display: {{ $journal_hide ? 'none' : 'block' }}">
                                                    <h2 class="accordion-header" id="headingTime">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTime" aria-expanded="true" aria-controls="collapseTime">
                                                            Time
                                                        </button>
                                                    </h2>
                                                    <div id="collapseTime" class="accordion-collapse collapse" aria-labelledby="headingTime">
                                                        <div class="accordion-body">
                                                            <div id="time_range_slider"></div>
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

                                                
                                                <div class="form-group accordion-item border mb-2" style="display: {{ $journal_hide ? 'none' : 'block' }}">
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
                                            <button id="clear_filter_button" type="button" style="color:#000!important" class="btn btn-link text-center mt-1 mb-2">Clear Filters</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div id="resultsContainer" class="col-lg-8 mx-auto"></div>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.0/nouislider.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        //init
        const isFavorites = {{ $is_favorites ? 'true' : 'false' }};
        var slider = null;
        if (!isFavorites) {
            var slider = document.getElementById('time_range_slider');
            var startTimeInput = document.getElementById('start_time_input');
            var endTimeInput = document.getElementById('end_time_input');

            //set up accordion
            var collapseTime = new bootstrap.Collapse(document.getElementById('collapseTime'), {
                toggle: false
            });
            var collapseCategory = new bootstrap.Collapse(document.getElementById('collapseCategory'), {
                toggle: false
            });
            var collapseModule = new bootstrap.Collapse(document.getElementById('collapseModule'), {
                toggle: false
            });
            var collapseFilter = new bootstrap.Collapse(document.getElementById('collapseFilter'), {
                toggle: false
            });
    
            //show/hide filter accordion
            const showFilterBtn = document.getElementById('showFilterButton');
            var open = false;
            showFilterBtn.addEventListener('click', function() {
                if (open) {
                    showFilterBtn.innerHTML = `<i class="bi bi-sliders" style="padding-right:10px"></i> Show Filters`;
                    open = false;
                }
                else {
                    showFilterBtn.innerHTML = `<i class="bi bi-sliders" style="padding-right:10px"></i> Hide Filters`;
                    open = true;
                }
            });
            function openFilters() {
                showFilterBtn.innerHTML = `<i class="bi bi-sliders" style="padding-right:10px"></i> Hide Filters`;
                open = true;
                collapseFilter.show();
            }

            //when apply search with filters
            document.getElementById('apply_filter_button').addEventListener('click', function() {
                search(true);
            });

            document.getElementById('clear_filter_button').addEventListener('click', clearFilters);
        }
        var sfForm = document.getElementById('search_filter_form');
        var searchBar = document.getElementById('search_bar');
        
        var baseParam = '{{ $base_param }}';
        const journalPage = baseParam === 'journal';
        
        //saved page number
        var _page = 1;

        const wipeFilters = {{ isset($wipe_filters) && $wipe_filters ? 'true' : 'false'}};

        //load in old filter values
        function loadFilters() {
            console.log('loading values');
            var filters = null;
            if (baseParam == 'main') {
                filters = JSON.parse(sessionStorage.getItem('main_filters'));
                console.log('main filters:', filters);
            }
            else if (baseParam == 'favorited') {
                filters = JSON.parse(sessionStorage.getItem('favorite_filters'));
            }
            else if (journalPage) {
                filters = JSON.parse(sessionStorage.getItem('journal_filters'));
            }
            if (filters) {
                //search
                searchBar.value = filters.search || '';
                searchBar.focus();

                if (!isFavorites) {
                    //remove transitions temporarily
                    document.querySelectorAll('.collapse, .arrow-selector').forEach(function(element) {
                        element.style.transition = 'none';
                    });
    
                    //time
                    startTimeInput.value = filters.start || 0;
                    endTimeInput.value = filters.end || 30;
                    if (filters.end != 30 || filters.start != 0) {
                        collapseTime.show();
                        openFilters();
                    }
                    
                    //categories
                    document.querySelectorAll('input[name="category[]"]').forEach(checkbox => {
                        checkbox.checked = filters.categories.includes(checkbox.value);
                        if (checkbox.checked) {
                            collapseCategory.show();
                            openFilters();
                        }
                    });
                    //modules
                    document.querySelectorAll('input[name="module[]"]').forEach(checkbox => {
                        checkbox.checked = filters.modules.includes(checkbox.value);
                        if (checkbox.checked) {
                            collapseModule.show();
                            openFilters();
                        }
                    });
                }

                //page
                _page = filters.page;
                // console.log('saved page: ', _page);

                //get smooth transitions back
                setTimeout(function() {
                    document.querySelectorAll('.collapse, .arrow-selector').forEach(function(element) {
                        element.style.transition = '';
                    });
                }, 10);
            }
        }
        if (!wipeFilters) {
            loadFilters();
        }
        else {
            if (baseParam == 'main') {
                filters = sessionStorage.removeItem('main_filters');
            }
            else if (baseParam == 'favorited') {
                filters = sessionStorage.removeItem('favorite_filters');
            }
            else if (journalPage) {
                filters = sessionStorage.removeItem('journal_filters');
            }
        }

        if (slider) {
            //SLIDER INIT
            //gets vals from previous request
            var startVal = startTimeInput.value || 0;
            var endVal = endTimeInput.value || 30;
            //format mins
            function minutesToTime(minutes) {
                return `${String(minutes)} mins`;
            }
            noUiSlider.create(slider, {
                start: [0, 30],
                connect: true,
                range: {
                    'min': 0,
                    'max': 30
                },
                step: 1,
                format: {
                    to: function (value) {
                        return minutesToTime(Math.round(value));
                    },
                    from: function (value) {
                        return value;
                    }
                }
            });
            var startLabel = document.getElementById('start_time_label');
            var endLabel = document.getElementById('end_time_label');

            //SLIDER CHANGE
            slider.noUiSlider.on('update', function (values) {
                //update labels
                startLabel.textContent = values[0];
                endLabel.textContent = values[1];
                
                //convert the # mins to #
                const timeToMinutes = (time) => {
                    const [mins, _] = time.split(' ').map(Number);
                    return mins;
                };
                
                //update hidden input
                startConverted = timeToMinutes(values[0]);
                endConverted = timeToMinutes(values[1]);
                startTimeInput.value = startConverted;
                endTimeInput.value = endConverted;
                //remove the inputs if default values
                if (startConverted === 0 && endConverted === 30) {
                    startTimeInput.remove();
                    endTimeInput.remove();
                } else {
                    //add inputs back on change
                    if (!sfForm.contains(startTimeInput)) {
                        sfForm.appendChild(startTimeInput);
                    }
                    if (!sfForm.contains(endTimeInput)) {
                        sfForm.appendChild(endTimeInput);
                    }
                }
            });
            
            //init labels
            slider.noUiSlider.set([parseInt(startVal), parseInt(endVal)]);
        }
        
        //APPLY/SAVE FILTERS - vars
        var _categories = null;
        var _modules = null;
        var _start = null;
        var _end = null;
        function saveFilters() {
            //save the filters (get filter vars)
            _categories = getChecked('categories');
            _modules = getChecked('modules');
            _start = startTimeInput.value;
            _end = endTimeInput.value;
        }

        //build query params
        function getQueryParams() {
            const params = new URLSearchParams();
            //search
            params.append('search', searchBar.value);
            
            if (!isFavorites) {
                //time
                if (_end != 30 || _start != 0) {
                    params.append('start_time', _start);
                    params.append('end_time', _end);
                }
                //categories and modules
                _categories.forEach(category => params.append('category[]', category));
                _modules.forEach(module_ => params.append('module[]', module_));
            }
            
            params.append('base_param', '{{ $base_param }}')
            //page
            params.append('page', _page);

            //saving recent search to session
            const filters = {
                search: searchBar.value,
                page: _page
            };
            if (!isFavorites) {
                filters.categories = _categories;
                filters.modules = _modules;
                filters.start = _start;
                filters.end = _end;
            }

            if (baseParam == 'main') {
                console.log('Saving filters to session:', filters);
                sessionStorage.setItem('main_filters', JSON.stringify(filters));
            }
            else if (baseParam == 'favorited') {
                console.log('Saving filters to session:', filters);
                sessionStorage.setItem('favorite_filters', JSON.stringify(filters));
            }
            else if (journalPage) {
                console.log('Saving filters to session:', filters);
                sessionStorage.setItem('journal_filters', JSON.stringify(filters));
            }
            
            return params.toString();
        }

        function getChecked(catOrMod) {
            //get all checked
            var checkboxes = null;
            if (catOrMod == 'modules') {
                checkboxes = document.querySelectorAll('input[name="module[]"]:checked');
            }
            else {
                checkboxes = document.querySelectorAll('input[name="category[]"]:checked');
            }
            const list = Array.from(checkboxes).map(checkbox => checkbox.value);
            return list;
        }


        //LOAD SEARCH
        function search(filters=false, first=false, isSearch=false) {
            //build url
            const searchUrl = new URL('{{ $page_info['search_route'] }}');
            //if changes in filter...
            if (!isFavorites && filters) {
                //save them onto the filter vars
                saveFilters();
            }
            if ((filters || isSearch) && !first) {
                //reset the page if filters or search, and not first load
                _page = 1;
            }

            const params = getQueryParams();
            searchUrl.search = params;
            console.log(params);
            //perform search
            fetch(searchUrl, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                console.log('AJAX success');
                //render component into container
                document.getElementById('resultsContainer').innerHTML = data.html;
                //if first render of page, show the filters and results - originally hidden
                if (first) {
                    document.getElementById('filterResultDiv').style.display = 'block';
                }
                if (journalPage) {
                    //init the read more buttons on the note results
                    initReadMore();
                }
                attachPaginationSearch();
            })
            .catch(error => {
                console.error('Error performing search', error);
            });
        }
        //search on page load with filters
        search(true, true);

        //PAGINATION - add event listeners to cancel pagination redirection and use search instead
        function attachPaginationSearch() {
            document.querySelectorAll('.pagination a').forEach(function (link) {
                link.addEventListener('click', function (event) {
                    //on click, prevent default, extract number, use in search
                    event.preventDefault();
                    const page = this.href.split('page=')[1];
                    _page = page;
                    search(false);
                });
            });
        }
        attachPaginationSearch();
        
        //SUBMISSION - filters (apply button)
        sfForm.addEventListener('submit', function(event) {
            event.preventDefault();
            search(true);
        });
        
        // keyboard submit on input - ignores filters
        // timeout to limit request rate
        let timeout = null;
        searchBar.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                search(isSearch=true);
            }, 750);
        });

        //CLEAR FILTERS - resubmit/search
        const moduleDiv = document.getElementById('module_check');
        const categoryDiv = document.getElementById('category_check');
        function clearFilters() {
            //clear the checkbox fields
            moduleDiv.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = false;
            });
            categoryDiv.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = false;
            });
            //resetting the time filter
            slider.noUiSlider.set([0, 30]);
            //submit
            search(true);
        }

        //CLEAR SEARCH - ignores filters
        document.getElementById('clear_search_button').addEventListener('click', clearSearch);
        function clearSearch() {
            searchBar.value = '';
            searchBar.focus();
            search(isSearch=true);
        }

        //NOTES
        //READ MORE
        function initReadMore() {
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
        }
    });
</script>
@endsection
