function initQuiz() {
    const quizForm = document.getElementById('quizForm');
    if (!quizForm) return;

    console.log('Initializing quiz...');
    let questionNumber = 1;
    const quizId = parseInt(quizForm.getAttribute('data-quiz-id') || '0', 10);
    const questionCount = parseInt(quizForm.getAttribute('data-question-count') || '1', 10);
    //for tracking user interaction
    const answerSet = new Set();
    const answersJson = quizForm.getAttribute('data-answers');
    const answers = answersJson ? JSON.parse(answersJson) : {};

    const prevQBtn = document.getElementById('prev_q_button');
    const nextQBtn = document.getElementById('next_q_button');
    const submitBtn = document.getElementById('submitButton');

    quizForm.addEventListener('submit', function(event) {
        event.preventDefault();
        console.log('submitting');
        submitAnswers();
    });

    if (prevQBtn) prevQBtn.addEventListener('click', function () { changeQuestion(questionNumber - 1); });
    if (nextQBtn) nextQBtn.addEventListener('click', function () { changeQuestion(questionNumber + 1); });

    // POPULATE ANSWERS
    function populateForm(answers) {
        for (const [key, value] of Object.entries(answers)) {
            if (typeof value === 'string') {
                const inputElement = document.querySelector(`[name="${key}"]`);
                if (inputElement) inputElement.value = value;
            } else if (typeof value === 'object') {
                document.querySelectorAll(`[name="${key}[]"]`).forEach(checkbox => {
                    checkBox(checkbox, value.includes(checkbox.value));
                });
            }
        }
    }
    populateForm(answers);

    // pause audios
    function pauseAudios() {
        document.querySelectorAll('audio').forEach(audio => { try { audio.pause(); } catch (_) {} });
    }

    // QUESTION CHANGE
    function changeQuestion(q_no) {
        console.log('Question No.: ' + q_no);
        questionNumber = q_no;
        const quizDivs = quizForm.querySelectorAll('.quiz-div');
        quizDivs.forEach(qDiv => {
            pauseAudios();
            const currentNumber = parseInt(qDiv.getAttribute('data-number'));
            const questionType = qDiv.getAttribute('data-type');
            const isLast = currentNumber === questionCount;
            const isFirst = currentNumber === 1;
            if (currentNumber === questionNumber) {
                qDiv.classList.remove('d-none');
                if (questionCount > 1) {
                    if (isFirst) prevQBtn?.setAttribute('disabled', ''); else prevQBtn?.removeAttribute('disabled');
                    if (isLast) {
                        nextQBtn?.classList.add('d-none');
                        submitBtn?.classList.remove('d-none');
                        if (questionType !== 'slider') nextQBtn?.setAttribute('disabled', '');
                    } else {
                        submitBtn?.classList.add('d-none');
                        nextQBtn?.classList.remove('d-none');
                        if (questionType !== 'slider') submitBtn?.setAttribute('disabled', '');
                    }
                }
                unlockNext(questionNumber);
            } else {
                console.log('hiding question ' + currentNumber);
                qDiv.classList.add('d-none');
            }
        });
    }
    changeQuestion(questionNumber);

    // UNLOCK NEXT/SUBMIT
    function unlockNext(questionNumber) {
        console.log('unlocking next for question ' + questionNumber);
        const questionDiv = document.getElementById('question_' + questionNumber);
        const questionType = questionDiv.getAttribute('data-type');
        nextQBtn?.setAttribute('disabled', '');
        submitBtn?.setAttribute('disabled', '');

        if (questionType === 'slider') {
            if (questionNumber === questionCount) submitBtn?.removeAttribute('disabled');
            else nextQBtn?.removeAttribute('disabled');
            return;
        }
        for (const check of questionDiv.querySelectorAll('.form-check-input')) {
            console.log(check.id);
            if (check.checked) {
                if (questionNumber === questionCount) submitBtn?.removeAttribute('disabled');
                else if (questionNumber < questionCount) nextQBtn?.removeAttribute('disabled');
                break;
            }
        }
    }

    // SHOWING FEEDBACK
    quizForm.querySelectorAll('.form-check-input').forEach(option => {
        option.addEventListener('change', function(event) {
            const splitId = event.target.id.split('_');
            const questionId = splitId[1];
            const optionId = splitId[2];
            const feedbackDiv = document.getElementById('feedback_' + questionId + '_' + optionId);
            const hasFeedback = feedbackDiv.getAttribute('data-show') === 'true';
            if (event.target.checked) {
                quizForm.querySelectorAll('.feedback-div').forEach(fbDiv => {
                    fbDiv.classList.add('d-none');
                    fbDiv.querySelectorAll('audio').forEach(audio => { try { audio.pause(); audio.currentTime = 0; } catch (_) {} });
                });
            }
            if (hasFeedback) {
                feedbackDiv.classList.toggle('d-none', !event.target.checked);
            }
            checkBox(option, event.target.checked);
        });
    });

    // SLIDER initialization for slider questions
    document.querySelectorAll('.quiz-div[data-type="slider"]').forEach(sliderDiv => {
        const qNum = sliderDiv.getAttribute('data-number');
        const sliderEl = document.getElementById('slider_' + qNum);
        const sliderVal = document.getElementById('slider_input_' + qNum).value;
        const hiddenInput = document.getElementById('slider_input_' + qNum);
        const loadingEl = document.getElementById('slider_loading_' + qNum);
        const bubble = document.getElementById('quiz_slider_bubble_' + qNum);

        const questionDataJson = sliderDiv.getAttribute('data-question-json');
        const questionData = questionDataJson ? JSON.parse(questionDataJson) : null;
        const sliderData = questionData?.options_feedback?.[0] || {};

        // want to show first and last pips on mobile, but keep the ticks
        const pipKeys = sliderData.pips ? Object.keys(sliderData.pips) : [];
        const firstPipValue = pipKeys.length > 0 ? pipKeys[0] : null;
        const lastPipValue = pipKeys.length > 0 ? pipKeys[pipKeys.length - 1] : null;

        let pipsConfig = undefined;
        if (sliderData.pips) {
            pipsConfig = {
                mode: 'values',
                values: Object.keys(sliderData.pips).map(Number),
                density: 4,
                format: {
                    to: function(value) {
                        if (window.innerWidth >= 768) return sliderData.pips[value];
                        if (value == firstPipValue || value == lastPipValue) return sliderData.pips[value];
                        return '';
                    }
                }
            };
        }

        if (window.noUiSlider) {
            window.noUiSlider.create(sliderEl, {
                start: [sliderVal],
                connect: 'lower',
                step: sliderData.step ?? 1,
                range: {
                    min: sliderData.min ?? 0,
                    max: sliderData.max ?? 100
                },
                pips: pipsConfig
            });
            let isUserInteracting = false;
            
            sliderEl.noUiSlider.on('start', function () {
                isUserInteracting = true;
            });
            
            sliderEl.noUiSlider.on('update', function (values, handle) {
                const value = Math.round(values[handle]);
                hiddenInput.value = value;
                const sliderRect = sliderEl.getBoundingClientRect();
                const handles = sliderEl.querySelectorAll('.noUi-handle');
                const activeHandle = handles[handle];
                if (activeHandle && bubble && isUserInteracting) {
                    const handleRect = activeHandle.getBoundingClientRect();
                    const left = handleRect.left + handleRect.width/2 - sliderRect.left;
                    bubble.style.left = left + 'px';
                    bubble.textContent = value + '%';
                    bubble.classList.remove('d-none');
                }
            });
            sliderEl.noUiSlider.on('end', function () { 
                isUserInteracting = false;
                if (bubble) bubble.classList.add('d-none'); 
            });
        }
        if (loadingEl) loadingEl.classList.add('d-none');
        sliderEl.classList.remove('d-none');
    });

    // ALL/NONE ABOVE
    quizForm.querySelectorAll('.quiz-div').forEach(quizDiv => {
        if (quizDiv.getAttribute('data-type') === 'checkbox') {
            const allBoxes = quizDiv.querySelectorAll('.form-check-input');
            allBoxes.forEach(option => {
                const behavior = option.getAttribute('above-behavior');
                option.addEventListener('click', function(event) {
                    const targetCheck = event.target.checked;
                    unlockNext(parseInt(quizDiv.getAttribute('data-number')));
                    if (targetCheck) {
                        if (behavior == 'none') {
                            allBoxes.forEach(box => { if (box != option) checkBox(box, false); });
                        } else {
                            allBoxes.forEach(box => {
                                if (behavior === 'all') checkBox(box, true);
                                if (box.getAttribute('above-behavior') === 'none') checkBox(box, false);
                            });
                        }
                    } else if (behavior != 'none') {
                        allBoxes.forEach(box => { if (box.getAttribute('above-behavior') === 'all') checkBox(box, false); });
                    }
                });
            });
        } else if (quizDiv.getAttribute('data-type') === 'radio') {
            const allBoxes = quizDiv.querySelectorAll('.form-check-input');
            allBoxes.forEach(option => {
                option.addEventListener('click', function() {
                    unlockNext(parseInt(quizDiv.getAttribute('data-number')));
                    allBoxes.forEach(box => { checkBox(box, box.checked); });
                });
            });
        }
    });

    // OTHER helper
    function checkBox(box, checked) {
        box.checked = checked;
        const splitId = box.id.split('_');
        const questionId = splitId[1];
        const optionId = splitId[2];
        if (box.getAttribute('data-other')) {
            const other = document.getElementById('other_' + questionId + '_' + optionId);
            if (other) checked ? other.removeAttribute('disabled') : other.setAttribute('disabled', '');
        }
    }

    // SUBMISSION
    function submitAnswers() {
        const formData = new FormData(quizForm);
        return new Promise((resolve, reject) => {
            window.axios.post('/quiz/' + quizId, formData)
                .then(response => {
                    console.log('Answers submitted!');
                    if (window.activityComplete) window.activityComplete();
                    resolve(true);
                })
                .catch(error => {
                    console.error('Error submitting answers: ', error);
                    const errorMessages = error.response?.data?.error_message || 'An unknown error occurred.';
                    if (window.showError) window.showError(errorMessages);
                    reject(false);
                });
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuiz);
} else {
    initQuiz();
}


