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
<div class="container">
    <div class="row">
        <div class="col-5">
            <form method="GET" action="{{ $page_info['search_route'] }}" style="display: {{ $page_info['first_empty'] ? 'none' : 'block'}};">
                <div class="input-group">
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder='{{ $page_info['search_text'] }}'>
                </div>
            </form>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <hr class="separator-line">
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <form method="GET" action="{{ $page_info['search_route'] }}" style="display: {{ $page_info['first_empty'] ? 'none' : 'block'}};">

                <div class="form-group mb-3">
                    <h5 for="time_range">Time</h5>
                    <div id="time_range_slider" style="margin-top: 20px;"></div>
                    <div class="d-flex justify-content-between">
                        <span id="start_time_label">0 min</span>
                        <span id="end_time_label">30 min</span>
                    </div>
                </div>
                <input type="hidden" name="start_time" id="start_time_input" value="{{ request('start_time') }}">
                <input type="hidden" name="end_time" id="end_time_input" value="{{ request('end_time') }}">
                
                <div class="form-group mb-3">
                    <h5>Category</h5>
                    @foreach ($categories as $category)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category[]" id="category_{{ strtolower($category) }}" value="{{ $category }}" {{ in_array($category, request('category', [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="category_{{ strtolower($category) }}">
                            {{ $category }}
                        </label>
                    </div>
                    @endforeach
                </div>
                    
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </form>
        </div>
        <div class="col-md-8">
            @if ($activities->isEmpty()) 
            <div class="text-left muted">
                {!! $page_info['empty'] !!}
            </div>
            @else
            <div class="">
                <div class="row mb-3 justify-content-center">
                    <div class="col-12">
                        <div class=" h-100">
                            @foreach ($activities as $activity)
                            <div class="card module p-2 mb-2">
                                <a class=" stretched-link w-100" href="{{ route('explore.activity', ['activity_id' => $activity->id, 'library' => true]) }}">
                                    {{ $activity->day->module->name }}, {{ $activity->day->name }} - {{ $activity->optional ? 'OPTIONAL: ' : '' }} {{ $activity->title }}
                                </a>
                                <i class="bi bi-arrow-right"></i>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
</div>
@endsection
<script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.8.0/nouislider.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var slider = document.getElementById('time_range_slider');
        var startTimeInput = document.getElementById('start_time_input');
        var endTimeInput = document.getElementById('end_time_input');

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

        //on change
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
            startTimeInput.value = timeToMinutes(values[0]);
            endTimeInput.value = timeToMinutes(values[1]);
        });

        //init labels
        slider.noUiSlider.set([parseInt(startVal), parseInt(endVal)]);
    });
</script>
