@if (isset($quiz))
    <div id="quiz-container" class="quiz-loading position-relative">
        <div id="quiz-throbber" class="quiz-throbber">
            <div class="spinner-border text-secondary" role="status">
                <span class="visually-hidden">Loading quiz...</span>
            </div>
        </div>
        <form id="quizForm" method="POST" class="pt-3" data-quiz-id="{{ $quiz->id }}" data-answers='@json($quiz->answers)' data-average="{{ $quiz->average }}">
            @csrf
            @foreach ($quiz->question_options as $question)
                @include('components.quiz.question', ['question' => $question, 'isFirst' => $question['number'] == 1, 'quizType' => $quiz->type, 'part' => $quiz->activity->day->module->id])
            @endforeach
            
            @include('components.quiz.navigation', ['hasMultipleQuestions' => $quiz->question_count > 1])
        </form>
    </div>
@endif