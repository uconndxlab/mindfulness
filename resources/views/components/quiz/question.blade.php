<div id="question_{{ $question['number'] }}" 
     class="quiz-div {{ $isFirst ? '' : 'd-none'}}" 
     data-number="{{ $question['number'] }}" 
     data-type="{{ $question['type'] }}" 
     @if ($question['type'] == 'slider') data-question-json='@json($question)' @endif>
     
    <div class="text-left quiz-question mb-3">
        @if ($quizType === 'check_in')
            <h3>In my last meditation practice (either the practice you just did in the app, or a practice that you did on your own):</h3>
        @elseif ($quizType === 'rate_my_awareness')
            <div class="markdown">
                <p>Great job on completing Part {{ $part }}! Let's reflect on the practice quality of your mindfulness meditation.</p>
                <h6>Read the statement below and move the slider to indicate the approximate percentage of time that your experience reflected that statement.</h6>
                <h6>Meditation experiences may vary, but for this reflection, think about a typical practice experience during Part {{ $part }}.</h6>
            </div>
        @else
            <h5>{{ $question['question'] }}</h5>
        @endif
    </div>

    @include('components.quiz.question-types', ['question' => $question])

    @if ($question['type'] == 'radio')
        @include('components.quiz.feedback', ['question' => $question])
    @endif
</div>
