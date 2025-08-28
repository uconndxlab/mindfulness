/**
 * QuizRadioQuestion - Handles radio button question logic
 * Simple interface: isAnswered(), getValue(), onAnswerChange(callback)
 */
class QuizRadioQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange; // callback for when isAnswered changes
        this.radioButtons = [];
        this.selectedValue = null;
        
        this.init();
    }

    init() {
        // Find all radio buttons for this question
        this.radioButtons = this.questionDiv.querySelectorAll('.form-check-input[type="radio"]');
        
        // Set up event listeners
        this.radioButtons.forEach(radio => {
            radio.addEventListener('click', () => this.handleRadioClick(radio));
        });
        
        console.log(`Radio question ${this.questionNumber} initialized with ${this.radioButtons.length} options`);
    }

    handleRadioClick(clickedRadio) {
        // Update selected value
        this.selectedValue = clickedRadio.value;
        
        // Handle feedback display
        this.showFeedback(clickedRadio);
        
        // Notify controller that answer changed
        this.onAnswerChange(this.questionNumber, this.isAnswered());
        
        console.log(`Radio question ${this.questionNumber} answered:`, this.selectedValue);
    }

    showFeedback(radio) {
        // Hide all feedback for this question first
        this.hideAllFeedback();
        
        // Show feedback for selected option
        const splitId = radio.id.split('_');
        const optionIndex = splitId[2];
        const feedbackDiv = document.getElementById(`feedback_${this.questionNumber}_${optionIndex}`);
        
        if (feedbackDiv && feedbackDiv.getAttribute('data-show') === 'true') {
            feedbackDiv.classList.remove('d-none');
        }
    }

    hideAllFeedback() {
        // Find all feedback divs for this question and hide them
        const allFeedbackDivs = document.querySelectorAll(`[id^="feedback_${this.questionNumber}_"]`);
        allFeedbackDivs.forEach(feedbackDiv => {
            feedbackDiv.classList.add('d-none');
            
            // Pause any audio in the feedback
            feedbackDiv.querySelectorAll('audio').forEach(audio => {
                try {
                    audio.pause();
                    audio.currentTime = 0;
                } catch (error) {
                    // Ignore audio errors
                }
            });
        });
    }

    // Interface methods that controller can use
    isAnswered() {
        return this.selectedValue !== null;
    }

    getValue() {
        return this.selectedValue;
    }

    // Method to set answer programmatically (for loading saved answers)
    setValue(value) {
        this.radioButtons.forEach(radio => {
            if (radio.value === value || radio.value === value.toString()) {
                radio.checked = true;
                this.selectedValue = value;
                this.showFeedback(radio);
            } else {
                radio.checked = false;
            }
        });
        
        console.log(`Radio question ${this.questionNumber} value set to:`, value);
    }

    // Reset the question
    reset() {
        this.radioButtons.forEach(radio => {
            radio.checked = false;
        });
        this.selectedValue = null;
        this.hideAllFeedback();
        
        // Notify controller
        this.onAnswerChange(this.questionNumber, false);
    }
}

export default QuizRadioQuestion;
