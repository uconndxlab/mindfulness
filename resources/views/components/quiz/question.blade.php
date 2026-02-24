<div id="question_{{ $question['number'] }}" 
     class="quiz-div {{ $isFirst ? '' : 'd-none'}}" 
     data-number="{{ $question['number'] }}" 
     data-type="{{ $question['type'] }}" 
     @if ($question['type'] == 'slider') data-question-json='@json($question)' @endif>
     
    <div class="d-flex align-items-center gap-2 quiz-question mb-3">
        @markdown($question['question'])
    </div>

    @include('components.quiz.question-types', ['question' => $question])

    @if ($question['type'] == 'radio')
        @include('components.quiz.feedback', ['question' => $question])
    @endif
</div>
