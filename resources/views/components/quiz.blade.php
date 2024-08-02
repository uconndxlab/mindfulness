@if (isset($quiz))
    <form id="quizForm" action="{{ route('quiz.submit', ['quiz_id' => $quiz->id]) }}" method="POST" class="pt-3">
        @csrf
        @foreach ($quiz->question_options as $key => $question)
            <div id="{{ $key }}" class="quiz-div" data-number="{{ $question['number'] }}" data-last="{{ $question['last'] ? 'true' : 'false' }}" data-type="{{ $question['type'] }}"style="display: {{ $question['number'] == 1 ? 'block' : 'none'}};">
                <div class="text-left quiz-question mb-3">
                    <h4>{{ $question['question'] }}</h4>
                </div>
                <!-- options -->
                @foreach ($question['options_feedback'] as $index => $option)
                    <div id="options_{{ $question['number'] }}" class="form-check type-{{ $question['type'] }}">
                        <input class="form-check-input" name="answer_{{ $question['number'] }}" above-behavior="{{ $option['above'] }}" type="{{ $question['type'] }}" id="option_{{ $question['number'] }}_{{ $index }}">
                        <label class="form-check-label" for="option_{{ $question['number'] }}_{{ $index }}">
                            {{ $option['option'] }}
                        </label>
                    </div>
                @endforeach

                <!-- feedback -->
                @foreach ($question['options_feedback'] as $index => $option)
                    @php
                        if ($option['correct']) {
                            $text_color = $option['correct'] == 1 ? 'text-success' : 'text-info';
                        }
                        else {
                            $text_color = 'text-danger';
                        }
                    @endphp
                    <div id="feedback_{{ $question['number'] }}_{{ $index }}" class="feedback-div" style="display: none;">
                        <div class="mt-3 {{ $text_color }}">
                            {{ $option['feedback'] }}
                        </div>
                        @if ($option['audio_path'])
                            <x-contentView id="fbAudio_{{ $question['number'] }}_{{ $index }}" id2="pdf_download" type="audio" file="{{ $option['audio_path'] }}"/>
                        @endif
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

            //SHOWING FEEDBACK
            quizForm.querySelectorAll('.form-check-input').forEach(option => {
                option.addEventListener('change', function(event) {
                    //build id and get div
                    const splitId = event.target.id.split('_');
                    const questionId = splitId[1];
                    const optionId = splitId[2];
                    const feedbackDiv = document.getElementById('feedback_' + questionId + '_' + optionId);
                    quizForm.querySelectorAll('.feedback-div').forEach(fbDiv => {
                        //hide all other feedback
                        fbDiv.style.display = 'none';
                        //pause any audio
                        fbDiv.querySelectorAll('audio').forEach(audio => {
                            audio.pause();
                        });
                    });
                    //show/hide feedback
                    feedbackDiv.style.display = event.target.checked ? 'block' : 'none';
                });
            });

            //ALL/NONE ABOVE
            //get all question divs
            quizForm.querySelectorAll('.quiz-div').forEach(quizDiv => {
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
                            else if (behavior != 'none') {
                                allBoxes.forEach(box => {
                                    if (box.getAttribute('above-behavior') === 'all') {
                                        box.checked = false;
                                    }
                                });
                            }
                        });
                    });
                }
            });
        });
    </script>
@endif