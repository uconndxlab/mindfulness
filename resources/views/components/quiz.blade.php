@if (isset($quiz))
    <div id="quiz-container">
        <form id="quizForm" method="POST" class="pt-3" data-quiz-id="{{ $quiz->id }}" data-answers='@json($quiz->answers)'>
            @csrf
            @foreach ($quiz->question_options as $question)
                @include('components.quiz.question', ['question' => $question, 'isFirst' => $question['number'] == 1])
            @endforeach
            
            @include('components.quiz.navigation', ['hasMultipleQuestions' => $quiz->question_count > 1])
        </form>
    </div>
@endif