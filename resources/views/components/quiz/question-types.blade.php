{{-- Radio Question --}}
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
@endif

{{-- Checkbox Question --}}
@if ($question['type'] === 'checkbox')
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
@endif

{{-- Slider Question --}}
@if ($question['type'] === 'slider')
    @php $value = $question['slider_config']['default'] ?? 50; @endphp
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
        <input type="hidden" 
               name="answer_{{ $question['number'] }}" 
               id="slider_input_{{ $question['number'] }}" 
               value="{{ $value }}">
    </div>
@endif
