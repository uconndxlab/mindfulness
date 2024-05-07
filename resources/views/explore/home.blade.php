@extends('layouts.main')

@section('title', 'Home')

@section('content')
<div>
    <p>Mindfulness Guides</p>

    <div class="container">
        @for ($i = 0; $i < 2; $i++)
        <div class="row mb-3 border justify-content-center d-flex align-items-stretch">
            @for ($j = 1; $j < 3; $j++)
            <div class="col-4">
                <div class="p-2 bg-light d-flex flex-column h-100">
                    <p>{{ 'Week '.($i * 2 + $j).':' }}</p>
                    @foreach (${'week'.($i * 2 + $j).'List'} as $item)
                        <div class="p-1">
                            <a class="btn btn-primary btn-block" href="{{ route('explore.weekly', ['contentKey' => 'week'.($i * 2 + $j).'-'.$item]) }}">{{ $item }}</a>
                        </div>
                    @endforeach
                </div>
            </div>
            @endfor
        </div>
        @endfor
    </div>
</div>
@endsection
