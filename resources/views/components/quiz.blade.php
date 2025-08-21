@if (isset($quiz))
    <form id="quizForm" method="POST" class="pt-3" data-quiz-id="{{ $quiz->id }}" data-question-count="{{ $quiz->question_count }}" data-answers='@json($quiz->answers)'>
        @csrf
        @foreach ($quiz->question_options as $key => $question)
            <div id="question_{{ $question['number'] }}" class="quiz-div {{ $question['number'] == 1 ? '' : 'd-none'}}" data-number="{{ $question['number'] }}" data-type="{{ $question['type'] }}" @if ($question['type'] == 'slider') data-question-json='@json($question)' @endif>
                <div class="text-left quiz-question mb-3">
                    <h4>{{ $question['question'] }}</h4>
                </div>

                @if ($question['type'] == 'checkbox' || $question['type'] == 'radio')
                    <!-- options -->
                    @foreach ($question['options_feedback'] as $index => $option)
                        <div id="options_{{ $question['number'] }}" class="form-check type-{{ $question['type'] }} mb-2">
                            <input class="form-check-input" name="answer_{{ $question['number'] }}[]" above-behavior="{{ $option['above'] }}" type="{{ $question['type'] }}" data-other="{{ $option['other'] }}" id="option_{{ $question['number'] }}_{{ $index }}" value="{{ $index }}">
                            <label class="form-check-label" for="option_{{ $question['number'] }}_{{ $index }}">
                                {{ $option['option'] }}
                            </label>
                            @if ($option['other'])
                                <div class="other-div">
                                    <input type="text" id="other_{{ $question['number'] }}_{{ $index }}" class="form-control" name="other_answer_{{ $question['number'] }}_{{ $index }}" placeholder="Please describe more..." disabled>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @elseif ($question['type'] == 'slider')
                    <!-- slider -->
                    @php
                        $slider_info = $question['options_feedback'][0];
                        $value = $slider_info['default'] ?? 50;
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
                    @foreach ($question['options_feedback'] as $index => $option)
                        @php
                            if ($option['correct']) {
                                $text_color = $option['correct'] == 1 ? 'text-success' : 'text-info';
                            }
                            else {
                                $text_color = 'text-danger';
                            }
                        @endphp
                        <div id="feedback_{{ $question['number'] }}_{{ $index }}" data-show="{{ !empty($option['feedback']) ? 'true' : 'false' }}" class="feedback-div mt-4 d-none">
                            @if ($option['audio_path'])
                                <x-contentView id="fbAudio_{{ $question['number'] }}_{{ $index }}" id2="pdf_download" type="feedback_audio" file="{{ $option['audio_path'] }}"/>
                            @endif
                            <div class="{{ $text_color }}">
                                @markdown(is_string($option['feedback'] ?? null) ? $option['feedback'] : '')
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
        @php
            $display = $quiz->question_count > 1 ? '' : 'd-none';
            $last = $quiz->question_count <=1 ? '' : 'd-none';
            $q1_slider = $quiz->question_options['question_1']['type'] == 'slider';
        @endphp
        <div class="d-flex justify-content-between quiz-nav-container">
            <button id="prev_q_button" type="button" class="btn-quiz {{ $display }}" disabled>
                <i class="bi bi-arrow-left"></i> Previous 
            </button>
            <button id="next_q_button" type="button" class="btn-quiz {{ $display }}" {{ $q1_slider ? '' : 'disabled' }}>
                Next <i class="bi bi-arrow-right "></i>
            </button>
            <button type="submit" id="submitButton" class="btn btn-primary ms-auto {{ $last }} mt-2 mb-0" {{ $q1_slider && $last ? '' : 'disabled' }}>
                Submit <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
@endif