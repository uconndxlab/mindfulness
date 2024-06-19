@extends('layouts.main')

@section('title', 'Meditation Library')

@section('content')
<div class="col-md-8">
    @php
    @endphp

    <div class="text-left">
        <h1 class="display fw-bold">Meditation Library</h1>
    </div>
    @if ($lessons->isEmpty())
        <div class="text-left muted">
            Keep progressing to unlock meditation sessions...
        </div>
    @else
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
                        
                            @foreach($lesson->main as $content_index => $content)
                                <div id="lesson_{{ $index }}_content_main_{{ $content_index }}" class="content-main-{{ $index }}" style="display: {{ $content_index == 0 ? 'block' : 'none' }};">
                                    <x-contentView id="lesson_{{ $index }}_content_view_{{ $content_index }}" id2="pdf_download" type="{{ $content->type }}" file="{{ $content->file_name }}"/>
                                </div>
                            @endforeach
                            @if ($lesson->main->count() > 1)
                                <div class="col-md-4 mb-4">
                                    <label class="fw-bold" for="word_otd">Select Voice:</label>



                                    <div class="form-group dropdown">
                                        <button id="dropdown_button_{{ $index }}" class="btn btn-xlight dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            {{ $lesson->main[0]->voice ? $lesson->main[0]->voice : "Other"}}
                                        </button>
                                        <ul class="dropdown-menu" id="voice_dropdown" name="voice_dropdown">
                                            @foreach ($lesson->main as $content_index => $content)
                                                <li>
                                                    <button class="dropdown-item" type="button" value="{{ $content_index }}" onclick="selectVoice({{ $index }}, {{ $content_index }}, '{{ $content->voice ? $content->voice : 'Other' }}')">
                                                        {{ $content->voice ? $content->voice : "Other" }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                        <input type="hidden" id="voice_select_{{ $index }}" name="voice_select_{{ $index }}" value="0">
                                    </div>


                                </div>
                            @endif

                            @foreach ($lesson->extra as $content_index => $item)
                                @if (isset($item->name))
                                    <h5>{{ $item->name }}</h5>
                                @endif
                                <x-contentView id="lesson_{{ $index }}_content_{{ $content_index }}" type="{{ $item->type }}" file="{{ $item->file_name }}"/>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
<script>


    //function for dropdown
    function selectVoice(lessonIndex, contentIndex, voice) {
        //change the text, change the value stored in hidden input
        document.getElementById(`dropdown_button_${lessonIndex}`).innerHTML = voice;
        document.getElementById(`voice_select_${lessonIndex}`).value = contentIndex;
        handleVoiceChange(lessonIndex);
    }

    //change voice
    function handleVoiceChange(lessonIndex) {
        //hide all options
        document.querySelectorAll('.content-main-' + lessonIndex).forEach(function(element) {
            element.style.display = 'none';
            element.querySelector('audio').pause();
        });

        //get select value
        var dd_input = document.getElementById('voice_select_' + lessonIndex);
        var selectedIndex = dd_input.value;

        //show the selected audio
        var selectedContent = document.getElementById('lesson_' + lessonIndex + '_content_main_' + selectedIndex);
        if (selectedContent) {
            selectedContent.style.display = 'block';
        }
    }

    //ON LOAD
    document.addEventListener('DOMContentLoaded', function() {
        //check count for all
        @foreach ($lessons as $index => $lesson)
            if ({{ $lesson->main->count() }} > 1) {
                const select = document.getElementById('voice_select_{{ $index }}');
                if (select) {
                    handleVoiceChange({{ $index }});
                }
            }
        @endforeach

        //pausing on accordian collapse
        const accordionCollapseElements = document.querySelectorAll('.accordion-collapse');
        accordionCollapseElements.forEach(function(collapseElement) {
            collapseElement.addEventListener('hidden.bs.collapse', function() {
                //pause audio elements inside
                const audioElements = collapseElement.querySelectorAll('audio');
                audioElements.forEach(function(audioElement) {
                    audioElement.pause();
                });
            });
        });
    });
</script>
@endsection
