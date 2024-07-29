@extends('layouts.main')

@section('title', $page_info['title'])

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.0/nouislider.min.css" rel="stylesheet">

<div class="col-md-8">
    @php
        $route_name = Request::route()->getName();
        $library = [false, false];
        if ($route_name == 'library.meditation') {
            $library[0] = true;
        }
        else {
            $library[1] = true;
        }
    @endphp

    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="tabs">
            <ul class="navbar-nav" style="flex-direction:row">
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link {{ $library[0] ? 'active disabled' : ''}}" href="{{ $library[0] ? '' : route('library.meditation') }}">Meditation</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link {{ $library[1] ? 'active disabled' : ''}}" href="{{ $library[1] ? '' : route('library.favorites') }}">Favorites</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="text-left">
        <h1 class="display fw-bold mb-4">{{ $page_info['title'] }}</h1>
    </div>
    @if ($page_info['first_empty'])
        <div class="text-left muted">
            {!! $page_info['first_empty'] !!}
        </div>
    @else
        <div class="">
            <form id="search_filter_form" method="GET" style="display: {{ $page_info['first_empty'] ? 'none' : 'block'}};">
                <div class="row">
                    <div class="col-md-6">
                        <div class="input-group mb-3">
                            <i style="padding:0px 10px" id="search-icon" class="bi bi-search"></i>
                            <input id="search_bar" type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder='{{ $page_info['search_text'] }}'>
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
            
                <div class="row search-filters">
                    <div class="col-md-4">
                        <div class="accordion accordion-flush mb-3" id="filter_accordion">
                            
                            @php
                                $show_time = (request('start_time') && request('start_time') != 0) || (request('end_time') && request('end_time') != 30);
                            @endphp
                            <div class="form-group accordion-item border mb-2">
                                <h2 class="accordion-header" id="headingTime">
                                    <button class="accordion-button {{ request('start_time') || request('end_time') ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTime" aria-expanded="true" aria-controls="collapseTime">
                                        Time
                                    </button>
                                </h2>
                                <div id="collapseTime" class="accordion-collapse collapse {{ request('start_time') || request('end_time') ? 'show' : '' }}" aria-labelledby="headingTime">
                                    <div class="accordion-body">
                                        <div id="time_range_slider"></div>
                                        <div class="d-flex justify-content-between">
                                            <span id="start_time_label">0 min</span>
                                            <span id="end_time_label">30 min</span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="start_time" id="start_time_input" value="{{ request('start_time') }}">
                                <input type="hidden" name="end_time" id="end_time_input" value="{{ request('end_time') }}">
                            </div>

                            <div class="form-group accordion-item border mb-2">
                                <h2 class="accordion-header" id="headingCategory">
                                    <button class="accordion-button {{ request('category') ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategory" aria-expanded="true" aria-controls="collapseCategory">
                                        Category
                                    </button>
                                </h2>
                                <div id="collapseCategory" class="accordion-collapse collapse {{ request('category') ? 'show' : '' }}" aria-labelledby="headingCategory">
                                    <div class="accordion-body">
                                        <div id="category_check">
                                            @foreach ($categories as $category)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="category[]" id="category_{{ strtolower($category) }}" value="{{ $category }}" {{ in_array($category, request('category', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="category_{{ strtolower($category) }}">
                                                        {{ $category }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group accordion-item border mb-2">
                                <h2 class="accordion-header" id="headingModule">
                                    <button class="accordion-button {{ request('module') ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseModule" aria-expanded="true" aria-controls="collapseModule">
                                        Module
                                    </button>
                                </h2>
                                <div id="collapseModule" class="accordion-collapse collapse {{ request('module') ? 'show' : '' }}" aria-labelledby="headingModule">
                                    <div class="accordion-body">
                                        <div id="module_check">
                                            @for ($i = 1; $i < 5; $i++)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="module[]" id="module_{{ $i }}" value="{{ $i }}" {{ in_array($i, request('module', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="module_{{ $i }}">
                                                        Module {{ $i }}
                                                    </label>
                                                </div>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                        <button id="clear_filter_button" type="button" style="color:#000!important" class="btn btn-link text-center mt-1 mb-2">Clear Filters</button>
                    </div>
                    <div id="activitiesContainer" class="col-md-8">
                        <x-search-results :activities="collect()"/>
                    </div>
                </div>
            </form>
        </div>
    @endif
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.0/nouislider.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var slider = document.getElementById('time_range_slider');
        var startTimeInput = document.getElementById('start_time_input');
        var endTimeInput = document.getElementById('end_time_input');

        var sfForm = document.getElementById('search_filter_form');

        var searchBar = document.getElementById('search_bar');
        
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
        

        //get params
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

        console.log('about to search...');
        //LOAD SEARCH
        function search() {
            //build query params
            const query = searchBar.value;
            const searchUrl = new URL('{{ route('library.search') }}');
            //search
            searchUrl.searchParams.append('search', query);
            //time
            var start = startTimeInput.value;
            var end = endTimeInput.value;
            if (end != 30 || start != 0) {
                searchUrl.searchParams.append('start_time', startTimeInput.value);
                searchUrl.searchParams.append('end_time', endTimeInput.value);
            }
            //categories and modules
            var categories = getChecked('categories');
            var modules = getChecked('modules');
            categories.forEach(category => searchUrl.searchParams.append('category[]', category));
            searchUrl.searchParams.append('category[]', '{{ $base_param }}')
            modules.forEach(module_ => searchUrl.searchParams.append('module[]', module_));

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
                document.getElementById('activitiesContainer').innerHTML = data.html;
            })
            .catch(error => {
                console.error('Error performing search', error);
            });
        }
        //search on page load
        search();
        
        //SUBMISSION
        sfForm.addEventListener('submit', function(event) {
            event.preventDefault();
            search();
        });
        
        // keyboard submit on input
        // timeout to limit request rate
        let timeout = null;
        searchBar.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(function() {
                search();
            }, 300);
        });

        //CLEAR FILTERS
        const moduleDiv = document.getElementById('module_check');
        const categoryDiv = document.getElementById('category_check');
        document.getElementById('clear_filter_button').addEventListener('click', clearFilters);
        function clearFilters() {
            //clear the checkbox fields
            moduleDiv.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = false;
            });
            categoryDiv.querySelectorAll('.form-check-input').forEach(checkbox => {
                checkbox.checked = false;
            });
            //resetting the time filter
            startTimeInput.remove();
            endTimeInput.remove();
            //submit
            search();
        }

        //CLEAR SEARCH
        document.getElementById('clear_search_button').addEventListener('click', clearSearch);
        function clearSearch() {
            searchBar.value = '';
            searchBar.focus();
            search();
        }
    });
</script>
@endsection
