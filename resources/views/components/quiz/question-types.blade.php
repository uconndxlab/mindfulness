@if ($question['type'] === 'radio')
    @foreach ($question['options'] as $option)
        <div class="form-check type-radio mb-2">
            <input class="form-check-input" 
                   name="answer_{{ $question['number'] }}" 
                   type="radio" 
                   data-other="{{ $option['allow_other'] ? 'true' : 'false' }}" 
                   id="option_{{ $question['number'] }}_{{ $option['id'] }}" 
                   value="{{ $option['id'] }}"
                   above-behavior="{{ $option['special_behavior'] }}">
            <label class="form-check-label" for="option_{{ $question['number'] }}_{{ $option['id'] }}">
                {{ $option['text'] }}
            </label>
            @if ($option['allow_other'])
                <div class="other-div">
                    <input type="text" 
                           id="other_{{ $question['number'] }}_{{ $option['id'] }}" 
                           class="form-control" 
                           placeholder="Please describe more..." 
                           disabled>
                </div>
            @endif
        </div>
    @endforeach
@elseif ($question['type'] === 'checkbox')
    @foreach ($question['options'] as $option)
        <div class="form-check type-checkbox mb-2">
            <input class="form-check-input" 
                   name="answer_{{ $question['number'] }}[]" 
                   type="checkbox" 
                   data-other="{{ $option['allow_other'] ? 'true' : 'false' }}" 
                   id="option_{{ $question['number'] }}_{{ $option['id'] }}" 
                   value="{{ $option['id'] }}"
                   above-behavior="{{ $option['special_behavior'] }}">
            <label class="form-check-label" for="option_{{ $question['number'] }}_{{ $option['id'] }}">
                {{ $option['text'] }}
            </label>
            @if ($option['allow_other'])
                <div class="other-div">
                    <input type="text" 
                           id="other_{{ $question['number'] }}_{{ $option['id'] }}" 
                           class="form-control" 
                           placeholder="Please describe more..." 
                           disabled>
                </div>
            @endif
        </div>
    @endforeach
@elseif ($question['type'] === 'slider')
    @foreach ($question['options'] as $index => $option)
        <hr>
        <label class="form-label">
            <div class="quiz-slider-label">
                @markdown($option['text'])
            </div>
        </label>
        <div class="quiz-slider slider-container noui-custom-pips">
            <div class="text-center slider-loading" id="slider_loading_{{ $question['number'] }}_{{ $option['id'] }}">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div class="position-relative">
                <div id="quiz_slider_bubble_{{ $question['number'] }}_{{ $option['id'] }}" 
                    class="slider-bubble d-none">{{ $option['slider_config']['default'] ?? 50 }}</div>
                <div id="slider_{{ $question['number'] }}_{{ $option['id'] }}" class="d-none no-interaction"></div>
            </div>
            <input type="hidden" 
                name="answer_{{ $question['number'] }}[{{ $option['id'] }}]" 
                id="slider_input_{{ $question['number'] }}_{{ $option['id'] }}" 
                value="{{ $option['slider_config']['default'] ?? 50 }}">
        </div>
        @if ($index === count($question['options']) - 1)
            <hr>
        @endif
    @endforeach
    <div class="d-flex justify-content-end">
        <div id="slider_average_display_{{ $question['number'] }}" class="text-muted d-none">
            <strong class="text-link"
                role="button"
                tabindex="0"
                data-bs-toggle="popover"
                data-bs-trigger="hover"
                data-bs-placement="top"
                data-bs-html="true"
                data-bs-title="Practice Quality"
                data-bs-content="This percentage reflects how consistently you returned to your present-moment experience during the practice. A higher score indicates you spent more time being aware and accepting, rather than avoiding or pushing away experiences.">
                <i class="bi bi-info-circle" ></i> 
                Practice Quality:
            </strong> 
            <span id="slider_average_value_{{ $question['number'] }}" class="pq-score">--</span>%
        </div>
    </div>
@elseif ($question['type'] === 'survey')
    @foreach ($question['options'] as $index => $option)
        <hr>
        <label class="form-label">
            <div class="quiz-survey-label">
                @markdown($option['text'])
            </div>
        </label>
        <div id="survey_{{ $question['number'] }}_{{ $option['id'] }}" class="quiz-survey mb-3">
            <div class="survey-btn-group d-flex" role="group">
                @foreach ($option['survey_config']['options'] ?? [] as $value => $label)
                    <label class="survey-btn flex-fill text-center">
                        <input type="radio" class="btn-check" 
                               name="answer_{{ $question['number'] }}[{{ $option['id'] }}]"
                               id="survey_{{ $question['number'] }}_{{ $option['id'] }}_{{ $value }}"
                               value="{{ $value }}" autocomplete="off">
                        <span class="survey-btn-inner d-block py-2 px-1">
                            <span class="survey-btn-value d-block fw-bold">{{ $value }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <div class="survey-legend d-flex">
                @foreach ($option['survey_config']['options'] ?? [] as $value => $label)
                    <span class="survey-legend-item flex-fill text-center small text-muted @if(!$loop->first && !$loop->last && $loop->iteration != 3) survey-legend-middle @endif">
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>
        @if ($index === count($question['options']) - 1)
            <hr>
        @endif
    @endforeach
    <div class="d-flex justify-content-end">
        <div id="survey_average_display_{{ $question['number'] }}" class="text-muted">
            <strong class="text-link"
                role="button"
                tabindex="0"
                data-bs-toggle="popover"
                data-bs-trigger="hover"
                data-bs-placement="top"
                data-bs-html="true"
                data-bs-title="Practice Quality"
                data-bs-content="This percentage reflects how consistently you returned to your present-moment experience during the practice. A higher score indicates you spent more time being aware and accepting, rather than avoiding or pushing away experiences.">
                <i class="bi bi-info-circle" ></i> 
                Practice Quality:
            </strong>
            <span id="survey_average_value_{{ $question['number'] }}" class="pq-score">--</span>%
        </div>
    </div>
@endif
