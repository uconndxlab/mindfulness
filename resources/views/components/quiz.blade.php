@if (isset($quiz))
    <form id="quizForm" action="{{ route('quiz.submit', ['quiz_id' => $quiz->id]) }}" method="POST" class="pt-3">
        @csrf
        @foreach ($quiz->question_options as $key => $question)
            <div id="question_{{ $question['number'] }}" class="quiz-div" data-number="{{ $question['number'] }}" data-type="{{ $question['type'] }}"style="display: {{ $question['number'] == 1 ? 'block' : 'none'}};">
                <div class="text-left quiz-question mb-3">
                    <h4>{{ $question['question'] }}</h4>
                </div>
                <!-- options -->
                @foreach ($question['options_feedback'] as $index => $option)
                    <div id="options_{{ $question['number'] }}" class="form-check type-{{ $question['type'] }}">
                        <input class="form-check-input" name="answer_{{ $question['number'] }}" above-behavior="{{ $option['above'] }}" type="{{ $question['type'] }}" data-other="{{ $option['other'] }}" id="option_{{ $question['number'] }}_{{ $index }}">
                        <label class="form-check-label" for="option_{{ $question['number'] }}_{{ $index }}">
                            {{ $option['option'] }}
                        </label>
                        @if ($option['other'])
                            <div class="other-div">
                                <input type="text" id="other_{{ $question['number'] }}_{{ $index }}" class="form-control" name="other_answer_{{ $question['number'] }}" placeholder="Please specify" disabled>
                            </div>
                        @endif
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
            <button id="prev_q_button" type="button" class="btn btn-primary" disabled>
                <i class="bi bi-arrow-left"></i>
            </button>
            <button id="next_q_button" type="button" class="btn btn-primary" disabled>
                <i class="bi bi-arrow-right"></i>
            </button>
        </div>
        <div class=" manual-margin-top">
            <button type="submit" id="submitButton" class="btn btn-secondary mt-4" disabled>SUBMIT</button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing quiz...');
            var questionNumber = 1;
            const questionCount = {{ $quiz->question_count }};
            //for tracking user interaction
            var answerSet = new Set();
    
            const quizForm = document.getElementById('quizForm');
            const prevQBtn = document.getElementById('prev_q_button');
            const nextQBtn = document.getElementById('next_q_button');
            const submitBtn = document.getElementById('submitButton');
    
            prevQBtn.addEventListener('click', function () {
                changeQuestion(questionNumber - 1);
            });
            nextQBtn.addEventListener('click', function () {
                changeQuestion(questionNumber + 1);
            });
    
            //QUESTION CHANGE
            function changeQuestion(q_no) {
                console.log('Question No.: ' + q_no);
                questionNumber = q_no;

                //get all questions
                const quizDivs = quizForm.querySelectorAll('.quiz-div');
                quizDivs.forEach(qDiv => {
                    //pause any audio
                    qDiv.querySelectorAll('audio').forEach(audio => {
                        audio.pause();
                    });
                    const currentNumber = parseInt(qDiv.getAttribute('data-number'));
                    const isLast = currentNumber === questionCount;
                    const isFirst = currentNumber === 1;
    
                    //handle hiding questions
                    if (currentNumber === questionNumber) {
                        qDiv.style.display = 'block';
                        //handle prev/next
                        if (isFirst) {
                            prevQBtn.setAttribute('disabled', '');
                        }
                        else {
                            prevQBtn.removeAttribute('disabled');
                        }
                        if (isLast) {
                            nextQBtn.setAttribute('disabled', '');
                        }
                        else {
                            nextQBtn.removeAttribute('disabled');
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
                    //build id and get question div
                    const splitId = event.target.id.split('_');
                    const questionId = splitId[1];
                    unlockSubmit(questionId);
                    const optionId = splitId[2];
                    const feedbackDiv = document.getElementById('feedback_' + questionId + '_' + optionId);
                    if (event.target.checked) {
                        quizForm.querySelectorAll('.feedback-div').forEach(fbDiv => {
                            //hide all other feedback
                            fbDiv.style.display = 'none';
                            //pause any audio
                            fbDiv.querySelectorAll('audio').forEach(audio => {
                                audio.pause();
                            });
                        });
                    }
                    //show/hide feedback
                    feedbackDiv.style.display = event.target.checked ? 'block' : 'none';

                    checkBox(option, event.target.checked);
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
                            const targetCheck = event.target.checked;
                            //checked
                            if (targetCheck) {
                                //none above
                                if (behavior == 'none') {
                                    allBoxes.forEach(box => {
                                        if (box != option) {
                                            checkBox(box, false);;
                                        }
                                    });
                                }
                                else {
                                    allBoxes.forEach(box => {
                                        //all above
                                        if (behavior === 'all') {
                                            checkBox(box, true);
                                        }
                                        //uncheck none above on all other checks
                                        if (box.getAttribute('above-behavior') === 'none') {
                                            checkBox(box, false);
                                        }
                                    });
                                }
                            }
                            //unchecked - any but none of the above
                            else if (behavior != 'none') {
                                allBoxes.forEach(box => {
                                    //uncheck all of above
                                    if (box.getAttribute('above-behavior') === 'all') {
                                        checkBox(box, false);
                                    }
                                });
                            }
                        });
                    });
                }
                //added in for other functionality on radio 
                else {
                    var allBoxes = quizDiv.querySelectorAll('.form-check-input');
                    allBoxes.forEach(option => {
                        option.addEventListener('click', function() {
                            allBoxes.forEach(box => {
                                checkBox(box, box.checked);
                            });
                        });
                    });
                }
            });

            //OTHER
            function checkBox(box, checked) {
                box.checked = checked;
                if (box.getAttribute('data-other')) {
                    const splitId = box.id.split('_');
                    const questionId = splitId[1];
                    const optionId = splitId[2];
                    const other = document.getElementById('other_'+questionId+'_'+optionId);
                    box.checked ? other.removeAttribute('disabled') : other.setAttribute('disabled', '');
                }
            }

            //SUBMISSION UNLOCK
            function unlockSubmit(question_no) {
                //adds each question number to track user interaction
                answerSet.add(question_no);
                if (answerSet.size == questionCount) {
                    submitBtn.removeAttribute('disabled');
                }
            }
        });
    </script>
@endif