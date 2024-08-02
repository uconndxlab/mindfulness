@if (isset($quiz))
    <form id="quizForm" action="{{ route('quiz.submit', ['quiz_id' => $quiz->id]) }}" method="POST" class="pt-3">
        @csrf
        @foreach ($quiz->question_options as $key => $question)
            <div id="{{ $key }}" class="quiz-div" data-number="{{ $question['number'] }}" data-last="{{ $question['last'] ? 'true' : 'false' }}" data-type="{{ $question['type'] }}"style="display: {{ $question['number'] == 1 ? 'block' : 'none'}};">
                <div class="text-left quiz-question mb-3">
                    <h2>{{ $question['question'] }}</h2>
                </div>
                @foreach ($question['options_feedback'] as $index => $option)
                    <div id="question_{{ $question['number'] }}_options" class="form-check type-{{ $question['type'] }}">
                        <input class="form-check-input" name="question_{{ $question['number'] }}answer" above-behavior="{{ $option['above'] }}" type="{{ $question['type'] }}" id="question_{{ $question['number'] }}_option_{{ $index }}">
                        <label class="form-check-label" for="question_{{ $question['number'] }}_option_{{ $index }}">
                            {{ $option['option'] }}
                        </label>
                    </div>
                @endforeach
            </div>
            @endforeach
        <div class="d-flex justify-content-between">
            <button id="prev_q_button" type="button" class="btn btn-primary disabled">
                <i class="bi bi-arrow-left"></i>
            </button>
            <button id="next_q_button" type="button" class="btn btn-primary disabled">
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing quiz...');
            var questionNumber = 1;
    
            const quizForm = document.getElementById('quizForm');
            const prevQBtn = document.getElementById('prev_q_button');
            const nextQBtn = document.getElementById('next_q_button');
    
            prevQBtn.addEventListener('click', function () {
                changeQuestion(questionNumber - 1);
            });
            nextQBtn.addEventListener('click', function () {
                changeQuestion(questionNumber + 1);
            });
    
            function changeQuestion(q_no) {
                console.log('Question No.: ' + q_no);
                questionNumber = q_no;
                const quizDivs = quizForm.querySelectorAll('.quiz-div');
                quizDivs.forEach(qDiv => {
                    const currentNumber = parseInt(qDiv.getAttribute('data-number'));
                    const isLast = qDiv.getAttribute('data-last') === 'true';
                    const isFirst = currentNumber === 1;
    
                    //handle hiding questions
                    if (currentNumber === questionNumber) {
                        qDiv.style.display = 'block';
                        //handle prev/next
                        if (isFirst) {
                            prevQBtn.classList.add('disabled');
                        }
                        else {
                            prevQBtn.classList.remove('disabled');
                        }
                        if (isLast) {
                            nextQBtn.classList.add('disabled');
                        }
                        else {
                            nextQBtn.classList.remove('disabled');
                        }
                    }
                    else {
                        qDiv.style.display = 'none';
                    }
                });
            }
            changeQuestion(questionNumber);

            //ALL/NONE ABOVE
            //get all question divs
            document.querySelectorAll('.quiz-div').forEach(quizDiv => {
                //select the ones with the checkbox answers
                if (quizDiv.getAttribute('data-type') === 'checkbox') {
                    //find all options within the question
                    var allBoxes = quizDiv.querySelectorAll('.form-check-input');
                    allBoxes.forEach(option => {
                        const behavior = option.getAttribute('above-behavior');
                        option.addEventListener('click', function(event) {
                            if (event.target.checked) {
                                if (behavior === 'none') {
                                    //uncheck all but this
                                    allBoxes.forEach(box => {
                                        if (box != option) {
                                            box.checked = false;
                                        }
                                    });
                                }
                                else {
                                    allBoxes.forEach(box => {
                                        //check all
                                        if (behavior === 'all') {
                                            box.checked = true;
                                        }
                                        //uncheck none above on all other checks
                                        if (box.getAttribute('above-behavior') === 'none') {
                                            box.checked = false;
                                        }
                                    });
                                }
                            }
                        });
                    });
                }
            });
        });
    </script>
@endif