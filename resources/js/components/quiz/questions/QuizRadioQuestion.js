class QuizRadioQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange;   // callback
        this.selectedValue = null;

        // cache all elements
        this.radioElements = new Map(); // value -> radio element
        this.feedbackElements = new Map();  // optionId -> feedback element
        this.otherInputElements = new Map(); // value -> other input element
        this.allAudioElements = [];
        
        this.batchInit();
    }

    batchInit() {
        const radioButtons = this.questionDiv.querySelectorAll('.form-check-input[type="radio"]');
        radioButtons.forEach(radio => {
            // cache radio by value
            this.radioElements.set(radio.value, radio);
            radio.addEventListener('click', () => this.handleRadioClick(radio));

            // cache other input elements if they exist
            if (radio.getAttribute('data-other') === 'true') {
                const optionId = this.extractOptionId(radio.id);
                const otherInput = this.questionDiv.querySelector(`#other_${this.questionNumber}_${optionId}`);
                if (otherInput) {
                    // map using radio value
                    this.otherInputElements.set(radio.value, otherInput);
                }
            }
        });

        const feedbackDivs = this.questionDiv.querySelectorAll('.feedback-div');
        
        feedbackDivs.forEach(feedbackDiv => {
            // get option id from feedback div id
            const optionId = this.extractOptionId(feedbackDiv.id);
            
            // cache feedback by option id
            this.feedbackElements.set(optionId, feedbackDiv);
            
            // cache audio elements
            const audioElements = feedbackDiv.querySelectorAll('audio');
            this.allAudioElements.push(...audioElements);
        });
    }

    extractOptionId(elementId) {
        // extract option id from element id (option_1_2 -> optionId = 2)
        return elementId.split('_')[2];
    }

    handleRadioClick(clickedRadio) {
        this.selectedValue = clickedRadio.value;
        this.showFeedback(clickedRadio);
        this.handleOtherInput(clickedRadio);
        // notify QuizController that answer changed
        this.onAnswerChange(this.questionNumber, this.isAnswered());
        console.log(`Radio question ${this.questionNumber} answered:`, this.selectedValue);
    }

    showFeedback(radio) {
        this.hideAllFeedback();
        
        // get option id from radio id
        const optionId = this.extractOptionId(radio.id);
        
        const feedbackDiv = this.feedbackElements.get(optionId);
        if (feedbackDiv && feedbackDiv.getAttribute('data-show') === 'true') {
            feedbackDiv.classList.remove('d-none');
        }
    }

    hideAllFeedback() {
        for (const feedbackDiv of this.feedbackElements.values()) {
            feedbackDiv.classList.add('d-none');
        }
        if (this.allAudioElements.length > 0) {
            this.pauseAllAudio();
        }
    }

    pauseAllAudio() {
        this.allAudioElements.forEach(audio => {
            try {
                audio.pause();
                audio.currentTime = 0;
            } catch (error) {
                // ignore audio errors
            }
        });
    }

    handleOtherInput(radio, otherText = null) {
        if (radio.getAttribute('data-other') === 'true') {
            const optionId = this.extractOptionId(radio.id);
            const otherInput = this.otherInputElements.get(optionId);
            
            if (otherInput) {
                if (radio.checked) {
                    otherInput.removeAttribute('disabled');
                    if (otherText !== null) {
                        otherInput.value = otherText;
                    }
                } else {
                    otherInput.setAttribute('disabled', '');
                }
            }
        }
    }

    // QuizController can use these methods
    isAnswered() {
        return this.selectedValue !== null;
    }

    getValue() {
        // return object format: {optionId: otherText} or {optionId: null}
        const result = {};

        if (this.selectedValue && this.otherInputElements.get(this.selectedValue)) {
            result[this.selectedValue] = this.otherInputElements.get(this.selectedValue).value;
        } else {
            result[this.selectedValue] = null;
        }

        return result;
    }

    // loading saved answers
    setValue(value) {
        this.reset();
        const optionId = Object.keys(value)[0];
        const otherText = Object.values(value)[0];
        
        const targetRadio = this.radioElements.get(optionId);

        if (targetRadio) {
            targetRadio.checked = true;
            this.selectedValue = optionId;
            this.showFeedback(targetRadio);
            if (targetRadio.getAttribute('data-other') === 'true') {
                this.handleOtherInput(targetRadio, otherText ?? '');
            }
        }
    }

    // reset the question
    reset() {
        for (const radio of this.radioElements.values()) {
            radio.checked = false;
        }
        
        this.selectedValue = null;
        this.hideAllFeedback();
        
        this.onAnswerChange(this.questionNumber, false);
    }
}

export default QuizRadioQuestion;
