/**
 * QuizCheckboxQuestion - Handles checkbox question logic with all/none behavior
 * Simple interface: isAnswered(), getValue(), setValue(), onAnswerChange(callback)
 */
class QuizCheckboxQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange; // Callback to notify controller
        this.checkboxes = [];
        this.selectedValues = new Set();
        
        this.init();
    }

    init() {
        // Find all checkboxes for this question
        this.checkboxes = this.questionDiv.querySelectorAll('.form-check-input[type="checkbox"]');
        
        // Set up event listeners
        this.checkboxes.forEach(checkbox => {
            checkbox.addEventListener('click', (event) => this.handleCheckboxClick(event, checkbox));
        });
        
        console.log(`Checkbox question ${this.questionNumber} initialized with ${this.checkboxes.length} options`);
    }

    handleCheckboxClick(event, clickedCheckbox) {
        const isChecked = event.target.checked;
        const behavior = clickedCheckbox.getAttribute('above-behavior');
        
        // Handle all/none behavior first
        if (isChecked) {
            this.selectedValues.add(clickedCheckbox.value);
            
            if (behavior === 'none') {
                // If "none of the above" is selected, uncheck all other options
                this.checkboxes.forEach(checkbox => {
                    if (checkbox !== clickedCheckbox) {
                        this.setCheckboxState(checkbox, false);
                        this.selectedValues.delete(checkbox.value);
                    }
                });
            } else if (behavior === 'all') {
                // If "all of the above" is selected, check all other options (except none)
                this.checkboxes.forEach(checkbox => {
                    if (checkbox.getAttribute('above-behavior') !== 'none') {
                        this.setCheckboxState(checkbox, true);
                        this.selectedValues.add(checkbox.value);
                    } else {
                        this.setCheckboxState(checkbox, false);
                        this.selectedValues.delete(checkbox.value);
                    }
                });
            } else {
                // Regular option selected - uncheck "none of the above" if it exists
                this.checkboxes.forEach(checkbox => {
                    if (checkbox.getAttribute('above-behavior') === 'none') {
                        this.setCheckboxState(checkbox, false);
                        this.selectedValues.delete(checkbox.value);
                    }
                });
            }
        } else {
            // Checkbox unchecked
            this.selectedValues.delete(clickedCheckbox.value);
            
            if (behavior === 'all') {
                // If "all of the above" is unchecked, uncheck all other non-none options
                this.checkboxes.forEach(checkbox => {
                    if (checkbox.getAttribute('above-behavior') !== 'none' && checkbox !== clickedCheckbox) {
                        this.setCheckboxState(checkbox, false);
                        this.selectedValues.delete(checkbox.value);
                    }
                });
            }
        }
        
        // Handle other text inputs
        this.handleOtherInput(clickedCheckbox);
        
        // Handle feedback
        this.handleFeedback(clickedCheckbox, isChecked);
        
        // Notify controller
        this.onAnswerChange(this.questionNumber, this.isAnswered());
        
        console.log(`Checkbox question ${this.questionNumber} selection changed:`, Array.from(this.selectedValues));
    }

    setCheckboxState(checkbox, checked) {
        checkbox.checked = checked;
        this.handleOtherInput(checkbox);
        this.handleFeedback(checkbox, checked);
    }

    handleOtherInput(checkbox) {
        if (checkbox.getAttribute('data-other') === 'true') {
            const splitId = checkbox.id.split('_');
            const optionIndex = splitId[2];
            const otherInput = document.getElementById(`other_${this.questionNumber}_${optionIndex}`);
            
            if (otherInput) {
                if (checkbox.checked) {
                    otherInput.removeAttribute('disabled');
                } else {
                    otherInput.setAttribute('disabled', '');
                    otherInput.value = ''; // Clear when disabled
                }
            }
        }
    }

    handleFeedback(checkbox, isChecked) {
        const splitId = checkbox.id.split('_');
        const optionIndex = splitId[2];
        const feedbackDiv = document.getElementById(`feedback_${this.questionNumber}_${optionIndex}`);
        
        if (feedbackDiv && feedbackDiv.getAttribute('data-show') === 'true') {
            if (isChecked) {
                // Hide all other feedback first
                this.hideAllFeedback();
                // Show this feedback
                feedbackDiv.classList.remove('d-none');
            } else {
                // Hide this feedback
                feedbackDiv.classList.add('d-none');
                if (window.pauseAllAudio) window.pauseAllAudio();
            }
        }
    }

    hideAllFeedback() {
        // Find all feedback divs for this question and hide them
        const allFeedbackDivs = document.querySelectorAll(`[id^="feedback_${this.questionNumber}_"]`);
        allFeedbackDivs.forEach(feedbackDiv => {
            feedbackDiv.classList.add('d-none');
        });
        
        if (window.pauseAllAudio) window.pauseAllAudio();
    }

    // Interface methods that controller can use
    isAnswered() {
        return this.selectedValues.size > 0;
    }

    getValue() {
        return Array.from(this.selectedValues);
    }

    // Method to set answer programmatically (for loading saved answers)
    setValue(values) {
        // Reset first
        this.reset();
        
        // Convert to array if it's an object
        let valuesToSet = [];
        if (Array.isArray(values)) {
            valuesToSet = values;
        } else if (typeof values === 'object' && values !== null) {
            valuesToSet = Object.values(values);
        } else {
            valuesToSet = [values];
        }
        
        // Set the specified values
        this.checkboxes.forEach(checkbox => {
            const shouldCheck = valuesToSet.includes(checkbox.value) || 
                               valuesToSet.includes(parseInt(checkbox.value));
            
            if (shouldCheck) {
                checkbox.checked = true;
                this.selectedValues.add(checkbox.value);
                this.handleOtherInput(checkbox);
                this.handleFeedback(checkbox, true);
            }
        });
        
        console.log(`Checkbox question ${this.questionNumber} values set to:`, valuesToSet);
    }

    // Reset the question
    reset() {
        this.checkboxes.forEach(checkbox => {
            checkbox.checked = false;
            this.handleOtherInput(checkbox);
        });
        
        this.selectedValues.clear();
        this.hideAllFeedback();
        
        // Notify controller
        this.onAnswerChange(this.questionNumber, false);
    }
}

export default QuizCheckboxQuestion;
