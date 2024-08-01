@if (isset($quiz))
    <form id="quizForm" action="{{ route('quiz.submit', ['quiz_id' => $quiz_id]) }}" method="POST" class="pt-3">
        @csrf
        @foreach ($quiz as $key => $question_options)
            <div id="{{ $key }}" class="quiz-div" data-number="{{ $question_options['number'] }}" data-last="{{ $question_options['last'] ? 'true' : 'false' }}">
                <div class="text-left quiz-question mb-3">
                    <h2>{{ $question_options['question'] }}</h2>
                </div>

                


            </div>
            @endforeach
        <div class="d-flex justify-content-between">
            <button id="prev_q_button" type="button" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i>
            </button>
            <button id="next_q_button" type="button" class="btn btn-primary">
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
@endif
<script>
    var prevQBtn;
    var nextQBtn;
    var quizForm;
    var questionNumber;

    function initializeQuiz() {
        questionNumber = 1;

        quizForm = document.getElementById('quizForm');
        prevQBtn = document.getElementById('prev_q_button');
        nextQBtn = document.getElementById('next_q_button');

        prevQBtn.addEventListener('click', function () {
            changeQuestion(questionNumber - 1);
        });
        nextQBtn.addEventListener('click', function () {
            changeQuestion(questionNumber + 1);
        });
        changeQuestion(questionNumber);
    }

    function changeQuestion(q_no = 1) {
        questionNumber = q_no;
        quizForm.querySelectorAll('.quiz-div').forEach(qDiv => {
            const currentNumber = parseInt(qDiv.getAttribute('data-number'));
            //handle hiding qs
            if (currentNumber === questionNumber) {
                qDiv.style.display = 'block';
                //handle the next/prev buttons
                if (qDiv.getAttribute('data-last') === 'true') {
                    nextQBtn.classList.add('disabled');
                }
                else {
                    nextQBtn.classList.remove('disabled');
                }
                if (questionNumber === 1) {
                    prevQBtn.classList.add('disabled');
                }
                else {
                    prevQBtn.classList.remove('disabled');
                }
            }
            else {
                qDiv.style.display = 'none';
            }
        });
    }
</script>