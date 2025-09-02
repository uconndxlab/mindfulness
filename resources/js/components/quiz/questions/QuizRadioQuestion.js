class QuizRadioQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange;   // callback
        this.selectedValue = null;

        // cache all elements
        this.radioElements = new Map(); // value -> radio element
        this.feedbackElements = new Map();  // optionId -> feedback element
        this.allAudioElements = [];
        
        this.batchInit();
    }

    batchInit() {
        const radioButtons = this.questionDiv.querySelectorAll('.form-check-input[type="radio"]');
        radioButtons.forEach(radio => {
            // cache radio by value
            this.radioElements.set(radio.value, radio);
            radio.addEventListener('click', () => this.handleRadioClick(radio));
        });

        const feedbackDivs = this.questionDiv.querySelectorAll('.feedback-div');
        
        feedbackDivs.forEach(feedbackDiv => {
            // get option id from feedback div id
            const idParts = feedbackDiv.id.split('_');
            const optionId = idParts[2];
            
            // cache feedback by option id
            this.feedbackElements.set(optionId, feedbackDiv);
            
            // cache audio elements
            const audioElements = feedbackDiv.querySelectorAll('audio');
            this.allAudioElements.push(...audioElements);
        });
    }

    handleRadioClick(clickedRadio) {
        this.selectedValue = clickedRadio.value;
        this.showFeedback(clickedRadio);
        // notify QuizController that answer changed
        this.onAnswerChange(this.questionNumber, this.isAnswered());
        console.log(`Radio question ${this.questionNumber} answered:`, this.selectedValue);
    }

    showFeedback(radio) {
        this.hideAllFeedback();
        
        // get option id from radio id
        const radioIdParts = radio.id.split('_');
        const optionId = radioIdParts[2];
        
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

    // QuizController can use these methods
    isAnswered() {
        return this.selectedValue !== null;
    }

    getValue() {
        return this.selectedValue;
    }

    // loading saved answers
    setValue(value) {
        const valueStr = value.toString();

        for (const radio of this.radioElements.values()) {
            radio.checked = false;
        }
        
        const targetRadio = this.radioElements.get(valueStr);
        if (targetRadio) {
            targetRadio.checked = true;
            this.selectedValue = value;
            this.showFeedback(targetRadio);
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
