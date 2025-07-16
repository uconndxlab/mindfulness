@if (isset($quiz))
    <form id="quizForm" method="POST" class="pt-3">
        @csrf
        @foreach ($quiz->question_options as $key => $question)
            <div id="question_{{ $question['number'] }}" class="quiz-div" data-number="{{ $question['number'] }}" data-type="{{ $question['type'] }}" style="display: {{ $question['number'] == 1 ? 'block' : 'none'}};">
                <div class="text-left quiz-question mb-3">
                    <h4>{{ $question['question'] }}</h4>
                </div>

                @if ($question['type'] == 'checkbox' || $question['type'] == 'radio')
                    <!-- options -->
                    @foreach ($question['options_feedback'] as $index => $option)
                        <div id="options_{{ $question['number'] }}" class="form-check type-{{ $question['type'] }} mb-2">
                            <input class="form-check-input" name="answer_{{ $question['number'] }}[]" above-behavior="{{ $option['above'] }}" type="{{ $question['type'] }}" data-other="{{ $option['other'] }}" id="option_{{ $question['number'] }}_{{ $index }}" value="{{ $index }}">
                            <label class="form-check-label" for="option_{{ $question['number'] }}_{{ $index }}">
                                {{ $option['option'] }}
                            </label>
                            @if ($option['other'])
                                <div class="other-div">
                                    <input type="text" id="other_{{ $question['number'] }}_{{ $index }}" class="form-control" name="other_answer_{{ $question['number'] }}_{{ $index }}" placeholder="Please describe more..." disabled>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @elseif ($question['type'] == 'slider')
                    <!-- slider -->
                    @php
                        $slider_info = $question['options_feedback'][0];
                        $value = $slider_info['default'] ?? 50;
                    @endphp
                    <div class="slider-container">
                        <div class="text-center slider-loading" id="slider_loading_{{ $question['number'] }}">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="slider_{{ $question['number'] }}" style="visibility: hidden;"></div>
                        <input type="hidden" name="answer_{{ $question['number'] }}" id="slider_input_{{ $question['number'] }}" value="{{ $value }}">
                    </div>
                @endif


                <!-- feedback -->
                @if ($question['type'] == 'radio' || $question['type'] == 'checkbox')
                    @foreach ($question['options_feedback'] as $index => $option)
                        @php
                            if ($option['correct']) {
                                $text_color = $option['correct'] == 1 ? 'text-success' : 'text-info';
                            }
                            else {
                                $text_color = 'text-danger';
                            }
                        @endphp
                        <div id="feedback_{{ $question['number'] }}_{{ $index }}" data-show="{{ $option['feedback'] ? 'true' : 'false' }}" class="feedback-div mt-4" style="display: none;">
                            @if ($option['audio_path'])
                                <x-contentView id="fbAudio_{{ $question['number'] }}_{{ $index }}" id2="pdf_download" type="feedback_audio" file="{{ $option['audio_path'] }}"/>
                            @endif
                            <div class="{{ $text_color }}">
                                {!! $option['feedback'] !!}
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        @endforeach
        @php
            $display = $quiz->question_count > 1 ? 'block' : 'none';
            $last = $quiz->question_count <=1 ? 'block' : 'none';
            $q1_slider = $quiz->question_options['question_1']['type'] == 'slider';
        @endphp
        <div class="d-flex justify-content-between">
            <button id="prev_q_button" type="button" class="btn-quiz" disabled style="display: {{ $display }};">
                <i class="bi bi-arrow-left"></i> Previous 
            </button>
            <button id="next_q_button" type="button" class="btn-quiz" {{ $q1_slider ? '' : 'disabled' }} style="display: {{ $display }};">
                Next <i class="bi bi-arrow-right "></i>
            </button>
            <button type="submit" id="submitButton" class="btn btn-primary ms-auto" {{ $q1_slider && $last ? '' : 'disabled' }} style="display: {{ $last }};width:max-content!important;margin-top:20px!important;margin-top:20px;margin-bottom:0px;">
                Submit <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing quiz...');
            var questionNumber = 1;
            const quizId = {{ $quiz->id }}
            const questionCount = {{ $quiz->question_count }};
            //for tracking user interaction
            var answerSet = new Set();
            const answers = @json($quiz->answers);
    
            const quizForm = document.getElementById('quizForm');
            const prevQBtn = document.getElementById('prev_q_button');
            const nextQBtn = document.getElementById('next_q_button');
            const submitBtn = document.getElementById('submitButton');

            quizForm.addEventListener('submit', function(event) {
                event.preventDefault(); 
                console.log('submitting');
                submitAnswers();
            });
    
            prevQBtn.addEventListener('click', function () {
                changeQuestion(questionNumber - 1);
            });
            nextQBtn.addEventListener('click', function () {
                changeQuestion(questionNumber + 1);
            });

            //POPULATE ANSWERS
            function populateForm(answers) {
                //loop through answers
                for (const [key, value] of Object.entries(answers)) {
                    //handle text
                    if (typeof value === 'string') {
                        let inputElement = document.querySelector(`[name="${key}"]`);
                        inputElement.value = value;
                    }
                    //handle check and radio
                    else if (typeof value === 'object') {
                        document.querySelectorAll(`[name="${key}[]"]`).forEach(checkbox => {
                            //also handles unlocking other
                            checkBox(checkbox, value.includes(checkbox.value))
                        });
                    }
                }
            }
            populateForm(answers);

            //pause audios
            function pauseAudios() {
                document.querySelectorAll('audio').forEach(audio => {
                    audio.pause();
                });
            }
            
            //QUESTION CHANGE
            function changeQuestion(q_no) {
                console.log('Question No.: ' + q_no);
                questionNumber = q_no;
                
                //get all questions
                const quizDivs = quizForm.querySelectorAll('.quiz-div');
                quizDivs.forEach(qDiv => {
                    //pause any audio
                    pauseAudios();
                    const currentNumber = parseInt(qDiv.getAttribute('data-number'));
                    const questionType = qDiv.getAttribute('data-type');
                    const isLast = currentNumber === questionCount;
                    const isFirst = currentNumber === 1;
                    
                    //handle hiding questions
                    if (currentNumber === questionNumber) {
                        qDiv.style.display = 'block';
                        if (questionCount > 1) {
                            //handle prev
                            if (isFirst) {
                                prevQBtn.setAttribute('disabled', '');
                            }
                            else {
                                prevQBtn.removeAttribute('disabled');
                            }
                            if (isLast) {
                                nextQBtn.style.display = 'none';
                                submitBtn.style.display = 'block';
                                // slider cannot have next/submit disabled
                                if (questionType != 'slider') {
                                    nextQBtn.setAttribute('disabled', '');
                                }
                            }
                            else {
                                submitBtn.style.display = 'none';
                                nextQBtn.style.display = 'block';
                                if (questionType != 'slider') {
                                    submitBtn.setAttribute('disabled', '');
                                }
                            }
                        }
                        unlockNext(questionNumber);
                    }
                    else {
                        console.log('hiding question ' + currentNumber);
                        qDiv.style.display = 'none';
                    }
                    
                });
            }
            changeQuestion(questionNumber);

            //UNLOCK NEXT/SUBMIT
            function unlockNext(questionNumber) {
                console.log('unlocking next for question ' + questionNumber);
                //handle unlocking of next
                const questionDiv = document.getElementById('question_'+questionNumber);
                const questionType = questionDiv.getAttribute('data-type');
                nextQBtn.setAttribute('disabled', '');
                submitBtn.setAttribute('disabled', '');

                if (questionType === 'slider') {
                    if (questionNumber === questionCount) {
                        submitBtn.removeAttribute('disabled');
                    } else {
                        nextQBtn.removeAttribute('disabled');
                    }
                    return;
                }

                //if we find one option is selected, remove the disable from next/submit
                for (const check of questionDiv.querySelectorAll('.form-check-input')) {
                    console.log(check.id);
                    if (check.checked) {
                        if (questionNumber === questionCount) {
                            submitBtn.removeAttribute('disabled');
                        }
                        else if (questionNumber < questionCount) {
                            nextQBtn.removeAttribute('disabled');
                        }
                        break;
                    }
                }
            }

            //SHOWING FEEDBACK
            quizForm.querySelectorAll('.form-check-input').forEach(option => {
                option.addEventListener('change', function(event) {
                    //build id and get question div
                    const splitId = event.target.id.split('_');
                    const questionId = splitId[1];
                    const optionId = splitId[2];
                    const feedbackDiv = document.getElementById('feedback_' + questionId + '_' + optionId);
                    // check if feedback exists
                    const hasFeedback = feedbackDiv.getAttribute('data-show') === 'true';
                    if (event.target.checked) {
                        quizForm.querySelectorAll('.feedback-div').forEach(fbDiv => {
                            //hide all other feedback
                            fbDiv.style.display = 'none';
                            //pause any audio
                            fbDiv.querySelectorAll('audio').forEach(audio => {
                                audio.pause();
                                audio.currentTime = 0;
                            });
                        });
                    }
                    //show/hide feedback
                    if (hasFeedback) {
                        feedbackDiv.style.display = event.target.checked ? 'block' : 'none';
                    }
                    //autoplay??
                    checkBox(option, event.target.checked);
                });
            });

            //SLIDER
            quizForm.querySelectorAll('.quiz-div[data-type="slider"]').forEach(sliderDiv => {
                const questionNumber = sliderDiv.getAttribute('data-number');
                const sliderEl = document.getElementById('slider_' + questionNumber);
                const sliderVal = document.getElementById('slider_input_' + questionNumber).value;
                const hiddenInput = document.getElementById('slider_input_' + questionNumber);
                const loadingEl = document.getElementById('slider_loading_' + questionNumber);
                
                const questionData = @json($quiz->question_options)['question_'+questionNumber];
                const sliderData = questionData.options_feedback[0];

                let pipsConfig = undefined;
                if (sliderData.pips) {
                    pipsConfig = {
                        mode: 'values',
                        values: Object.keys(sliderData.pips).map(Number),
                        density: 4,
                        format: {
                            to: function(value) {
                                return sliderData.pips[value];
                            }
                        }
                    };
                }

                noUiSlider.create(sliderEl, {
                    start: [sliderVal],
                    connect: 'lower',
                    step: sliderData.step ?? 1,
                    range: {
                        'min': sliderData.min ?? 0,
                        'max': sliderData.max ?? 100
                    },
                    pips: pipsConfig
                });

                sliderEl.noUiSlider.on('update', function (values, handle) {
                    const value = Math.round(values[handle]);
                    hiddenInput.value = value;
                });

                if (loadingEl) {
                    loadingEl.style.display = 'none';
                }
                sliderEl.style.visibility = 'visible';
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
                            //handle unlocking of next
                            unlockNext(parseInt(quizDiv.getAttribute('data-number')));
                            //checked
                            if (targetCheck) {
                                //none above
                                if (behavior == 'none') {
                                    allBoxes.forEach(box => {
                                        if (box != option) {
                                            checkBox(box, false);
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
                else if (quizDiv.getAttribute('data-type') === 'radio') {
                    var allBoxes = quizDiv.querySelectorAll('.form-check-input');
                    allBoxes.forEach(option => {
                        option.addEventListener('click', function() {
                            //handle unlocking of next
                            unlockNext(parseInt(quizDiv.getAttribute('data-number')));
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
                const splitId = box.id.split('_');
                const questionId = splitId[1];
                const optionId = splitId[2];
                //handle other
                if (box.getAttribute('data-other')) {
                    const other = document.getElementById('other_'+questionId+'_'+optionId);
                    box.checked ? other.removeAttribute('disabled') : other.setAttribute('disabled', '');
                }
            }

            //SUBMISSION
            function submitAnswers() {
                const formData = new FormData(document.getElementById('quizForm'));
                return new Promise((resolve, reject) => {
                    axios.post('/quiz/' + quizId, formData, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        console.log('Answers submitted!');
                        activityComplete();
                        resolve(true);
                    })
                    .catch(error => {
                        console.error('Error submitting answers: ', error);
                        //display error
                        const errorMessages = error.response?.data?.error_message || 'An unknown error occurred.';
                        showError(errorMessages);
                        reject(false);
                    });
                });
            }
        });
    </script>
@endif