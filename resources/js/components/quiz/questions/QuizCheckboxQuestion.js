class QuizCheckboxQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange; // callback
        this.selectedValues = new Set();

        // cache all elements
        this.checkboxElements = new Map(); // value -> checkbox element
        this.otherInputElements = new Map(); // value -> other input element
        this.noneOfTheAboveCheckbox = [];
        this.allOfTheAboveCheckbox = [];

        this.batchInit();
    }

    batchInit() {
        const checkboxes = this.questionDiv.querySelectorAll('.form-check-input[type="checkbox"]');
        
        checkboxes.forEach(checkbox => {
            // cache checkbox by value
            this.checkboxElements.set(checkbox.value, checkbox);
            checkbox.addEventListener('click', () => this.handleCheckboxClick(checkbox));
            
            // categorize special behavior checkboxes
            const behavior = checkbox.getAttribute('above-behavior');
            if (behavior === 'none') {
                this.noneOfTheAboveCheckbox.push(checkbox);
            } else if (behavior === 'all') {
                this.allOfTheAboveCheckbox.push(checkbox);
            }
            
            // cache other input elements if they exist
            if (checkbox.getAttribute('data-other') === 'true') {
                const optionId = this.extractOptionId(checkbox.id);
                const otherInput = this.questionDiv.querySelector(`#other_${this.questionNumber}_${optionId}`);
                if (otherInput) {
                    // map using checkbox value
                    this.otherInputElements.set(checkbox.value, otherInput);
                }
            }
        });
        
        console.log(`Checkbox question ${this.questionNumber} initialized: ${checkboxes.length} options, ${this.otherInputElements.size} other inputs`);
    }

    extractOptionId(elementId) {
        // extract option id from element id (option_1_2 -> optionId = 2)
        return elementId.split('_')[2];
    }

    handleCheckboxClick(clickedCheckbox) {
        const isChecked = clickedCheckbox.checked;
        const behavior = clickedCheckbox.getAttribute('above-behavior');
        
        // handle all/none behavior
        if (isChecked) {
            if (behavior === 'none') {
                // "none of the above"
                for (const checkbox of this.checkboxElements.values()) {
                    if (checkbox !== clickedCheckbox) {
                        this.setCheckboxState(checkbox, false);
                    }
                    else {
                        this.setCheckboxState(checkbox, true);
                    }
                }
            } else if (behavior === 'all') {
                // "all of the above"
                for (const [val, checkbox] of this.checkboxElements) {
                    if (checkbox.getAttribute('above-behavior') !== 'none') {
                        this.setCheckboxState(checkbox, true);
                    } else {
                        this.setCheckboxState(checkbox, false);
                    }
                }
            } else {
                // regular option selected - uncheck "none of the above" if it exists
                this.selectedValues.add(clickedCheckbox.value);
                this.noneOfTheAboveCheckbox.forEach(checkbox => {
                    this.setCheckboxState(checkbox, false);
                });
            }
        } else {
            // checkbox unchecked
            this.setCheckboxState(clickedCheckbox, false);

            // find all of the above checkboxes and uncheck them
            this.allOfTheAboveCheckbox.forEach(checkbox => {
                this.setCheckboxState(checkbox, false);
            });
        }

        this.handleOtherInput(clickedCheckbox);
        
        // notify QuizController
        this.onAnswerChange(this.questionNumber, this.isAnswered());
        console.log(`Checkbox question ${this.questionNumber} selection changed:`, Array.from(this.selectedValues));
    }

    setCheckboxState(checkbox, checked) {
        checkbox.checked = checked;
        // update selected values set
        if (checked) {
            this.selectedValues.add(checkbox.value);
        } else {
            this.selectedValues.delete(checkbox.value);
        }
        this.handleOtherInput(checkbox);
    }

    handleOtherInput(checkbox, otherText = null) {
        if (checkbox.getAttribute('data-other') === 'true') {
            const optionId = this.extractOptionId(checkbox.id);
            const otherInput = this.otherInputElements.get(optionId);
            
            if (otherInput) {
                if (checkbox.checked) {
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

    // Interface methods that controller can use
    isAnswered() {
        return this.selectedValues.size > 0;
    }

    getValue() {
        const result = [];
        
        for (const value of this.selectedValues) {
            const checkbox = this.checkboxElements.get(value);
            const item = {};
            
            if (checkbox && checkbox.getAttribute('data-other') === 'true') {
                // need to include other text
                const optionId = this.extractOptionId(checkbox.id);
                const otherInput = this.otherInputElements.get(optionId);
                const otherText = otherInput ? otherInput.value.trim() : null;
                
                // return as object: {optionId: otherText}
                item[value] = otherText || null;
            } else {
                // regular options return as simple values
                item[value] = null;
            }
            result.push(item);
        }
        
        return result;
    }

    // load saved answers
    setValue(values) {
        this.reset();
        
        // handle different input formats
        let valuesToSet = [];
        if (Array.isArray(values)) {
            valuesToSet = values;
        } else if (typeof values === 'object' && values !== null) {
            valuesToSet = Object.values(values);
        } else {
            valuesToSet = [values];
        }
        
        for (const item of valuesToSet) {
            let optionId, otherText = null;
            
            if (typeof item === 'object' && item !== null) {
                // Format: {optionId: otherText} or {optionId: null}
                optionId = Object.keys(item)[0];
                otherText = Object.values(item)[0];
            } else {
                // format - simple value (string - optionId)
                optionId = item.toString();
            }
            
            const checkbox = this.checkboxElements.get(optionId);
            if (checkbox) {
                checkbox.checked = true;
                this.selectedValues.add(checkbox.value);
                if (checkbox.getAttribute('data-other') === 'true') {
                    this.handleOtherInput(checkbox, otherText ?? '');
                }
            }
        }
        
        console.log(`Checkbox question ${this.questionNumber} values set to:`, valuesToSet);
    }

    reset() {
        for (const checkbox of this.checkboxElements.values()) {
            checkbox.checked = false;
            this.handleOtherInput(checkbox);
        }
        
        this.selectedValues.clear();

        // notify QuizController
        this.onAnswerChange(this.questionNumber, false);
    }
}

export default QuizCheckboxQuestion;
