@extends('layouts.main')

@section('title', 'Meditation Library')

@section('content')
<div class="col-md-8">
    @php
    @endphp

    <div class="text-left">
        <h1 class="display fw-bold">Meditation Library</h1>
    </div>

    <div class="accordion border accordion-flush mb-3" id="accordionExample">
        @foreach ($lessons as $index => $lesson)
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_{{ $index }}" aria-expanded="false" aria-controls="collapse_{{ $index }}">
                        {{ $lesson->title }} - {{ $lesson->sub_header }}
                    </button>
                </h2>
                <div id="collapse_{{ $index }}" class="accordion-collapse collapse" aria-labelledby="heading_{{ $index }}" data-bs-parent="#accordionExample">
                    <div class="accordion-body">
                        @foreach ($lesson->content as $content_index => $item)
                            @if (isset($item->name))
                                <h5>{{ $item->name }}</h5>
                            @endif
                            <x-contentView id="lesson_{{ $index }}_content_{{ $content_index }}" type="{{ $item->type }}" file="{{ $item->file_name }}" />
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
