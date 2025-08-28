@if (isset($quiz))
    <form id="quizForm" method="POST" class="pt-3" data-quiz-id="{{ $quiz->id }}" data-answers='@json($quiz->answers)'>
        @csrf
        @foreach ($quiz->question_options as $question)
            <div id="question_{{ $question['number'] }}" class="quiz-div {{ $question['number'] == 1 ? '' : 'd-none'}}" data-number="{{ $question['number'] }}" data-type="{{ $question['type'] }}" @if ($question['type'] == 'slider') data-question-json='@json($question)' @endif>
                <div class="text-left quiz-question mb-3">
                    <h4>{{ $question['question'] }}</h4>
                </div>

                @if ($question['type'] == 'checkbox' || $question['type'] == 'radio')
                    <!-- options -->
                    @foreach ($question['options'] as $option)
                        <div id="options_{{ $question['number'] }}" class="form-check type-{{ $question['type'] }} mb-2">
                            <input class="form-check-input" name="answer_{{ $question['number'] }}{{ $question['type'] === 'checkbox' ? '[]' : '' }}" above-behavior="{{ $option['special_behavior'] }}" type="{{ $question['type'] }}" data-other="{{ $option['allow_other'] ? 'true' : 'false' }}" id="option_{{ $question['number'] }}_{{ $option['id'] }}" value="{{ $option['id'] }}">
                            <label class="form-check-label" for="option_{{ $question['number'] }}_{{ $option['id'] }}">
                                {{ $option['text'] }}
                            </label>
                            @if ($option['allow_other'])
                                <div class="other-div">
                                    <input type="text" id="other_{{ $question['number'] }}_{{ $option['id'] }}" class="form-control" name="other_answer_{{ $question['number'] }}_{{ $option['id'] }}" placeholder="Please describe more..." disabled>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @elseif ($question['type'] == 'slider')
                    <!-- slider -->
                    @php
                        $slider_config = $question['slider_config'];
                        $value = $slider_config['default'] ?? 50;
                    @endphp
                    <div class="slider-container noui-custom-pips">
                        <div class="text-center slider-loading" id="slider_loading_{{ $question['number'] }}">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div class="position-relative">
                            <div id="quiz_slider_bubble_{{ $question['number'] }}" class="slider-bubble d-none">{{ $value }}</div>
                            <div id="slider_{{ $question['number'] }}" class="d-none"></div>
                        </div>
                        <input type="hidden" name="answer_{{ $question['number'] }}" id="slider_input_{{ $question['number'] }}" value="{{ $value }}">
                    </div>
                @endif


                <!-- feedback -->
                @if ($question['type'] == 'radio' || $question['type'] == 'checkbox')
                    @foreach ($question['options'] as $option)
                        <div id="feedback_{{ $question['number'] }}_{{ $option['id'] }}" data-show="{{ !empty($option['feedback']) ? 'true' : 'false' }}" class="feedback-div mt-4 d-none">
                            @if ($option['audio_path'])
                                <x-contentView id="fbAudio_{{ $question['number'] }}_{{ $option['id'] }}" id2="pdf_download" type="feedback_audio" file="{{ $option['audio_path'] }}"/>
                            @endif
                            <div class="text-info">
                                @markdown(is_string($option['feedback'] ?? null) ? $option['feedback'] : '')
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
        @php
            $hasNext = $quiz->question_count > 1 ? '' : 'd-none';
        @endphp
        <div class="d-flex justify-content-between quiz-nav-container">
            <button id="prev_q_button" type="button" class="btn-quiz invisible" disabled>
                <i class="bi bi-arrow-left"></i> Previous 
            </button>
            <button id="next_q_button" type="button" class="btn-quiz {{ $hasNext }}" disabled>
                Next <i class="bi bi-arrow-right "></i>
            </button>
            <button type="submit" id="submitButton" class="btn btn-primary ms-auto {{ !$hasNext }} mt-2 mb-0" disabled>
                Submit <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
@endif