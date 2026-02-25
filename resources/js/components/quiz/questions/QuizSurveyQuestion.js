class QuizSurveyQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange, initialAverage = null) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange; // callback
        this.average = initialAverage;

        this.radioElements = new Map();   // optionId -> (value -> radio element)
        this.selectedValues = new Map();  // optionId -> selected value (string)
        this.inverseOptions = new Map();  // optionId -> boolean (inverse scoring)
        this.scaleMin = 1;
        this.scaleMax = 5;
        this.answered = false;

        const questionDataJson = this.questionDiv.getAttribute('data-question-json');
        this.questionData = questionDataJson ? JSON.parse(questionDataJson) : null;

        this.batchInit();
    }

    batchInit() {
        this.findAndCacheElements();
        this.answered = this.isAnswered();
        this.updateAverage();
        console.log(`Survey question ${this.questionNumber} initialized with ${this.radioElements.size} sub-question(s)`);
    }

    findAndCacheElements() {
        // find all elements based on the options structure
        const options = this.questionData?.options || [];
        
        // cache average display elements
        this.averageDisplayDiv = document.getElementById(`survey_average_display_${this.questionNumber}`);
        this.averageValueSpan = document.getElementById(`survey_average_value_${this.questionNumber}`);

        // determine scale range from first option's survey_config.options keys
        if (options.length > 0 && options[0].survey_config?.options) {
            const keys = Object.keys(options[0].survey_config.options).map(Number);
            this.scaleMin = Math.min(...keys);
            this.scaleMax = Math.max(...keys);
        }

        for (const option of options) {
            const optionId = option.id;
            const isInverse = option.inverse_score ?? false;
            this.inverseOptions.set(optionId, isInverse);

            // get the survey div for this option
            const surveyDiv = document.getElementById(`survey_${this.questionNumber}_${optionId}`);

            if (surveyDiv) {
                // iterate over the options for this question
                const radioButtons = surveyDiv.querySelectorAll('input[type="radio"]');
                const valueElements = new Map();

                radioButtons.forEach(radio => {
                    // cache radio by value
                    valueElements.set(radio.value, radio);
                    radio.addEventListener('change', () => this.handleRadioClick(optionId, radio));
                });
                // cache the value elements for this option (optionId -> (value -> radio element))
                this.radioElements.set(optionId, valueElements);
            }
        }
    }

    handleRadioClick(optionId, radio) {
        this.selectedValues.set(optionId, radio.value);
        // update average if fully answered
        const nowAnswered = this.isAnswered();
        if (nowAnswered) {
            this.updateAverage();
        }
        this.onAnswerChange(this.questionNumber, nowAnswered);
        console.log(`Survey question ${this.questionNumber} option ${optionId} answered:`, radio.value);
    }

    updateAverage() {
        if (!this.averageDisplayDiv || !this.averageValueSpan) return;

        let total = 0;
        let count = 0;

        for (const [optionId, value] of this.selectedValues) {
            let score = parseInt(value, 10) || 0;
            if (this.inverseOptions.get(optionId)) {
                score = this.scaleMax + this.scaleMin - score;
            }
            total += score;
            count++;
        }

        if (count > 0) {
            this.average = (total / count).toFixed(2);
            this.averageValueSpan.textContent = this.average;
        } else if (this.average !== null && this.average !== undefined) {
            this.averageValueSpan.textContent = this.average;
        }
    }

    // interface methods for QuizController
    isAnswered() {
        const options = this.questionData?.options || [];
        if (options.length === 0) return false;
        for (const option of options) {
            if (!this.selectedValues.has(option.id)) return false;
        }
        return true;
    }

    getAverage() {
        return this.average;
    }

    getValue() {
        // return array format: [{"0": 4}, {"1": 2}]
        const result = [];
        for (const [optionId, value] of this.selectedValues) {
            result.push({ [optionId]: parseInt(value, 10) });
        }
        return result;
    }

    // load saved answers
    setValue(values) {
        // handle array format: [{"1": 0}, {"2": 2}]
        if (!Array.isArray(values)) return;
        for (const item of values) {
            const optionId = parseInt(Object.keys(item)[0], 10);
            const value = Object.values(item)[0];
            this.setSingleSurveyValue(optionId, value);
        }
        this.answered = this.isAnswered();
        this.updateAverage();
        console.log(`Survey question ${this.questionNumber} values set to:`, JSON.stringify(values));
    }

    setSingleSurveyValue(optionId, value) {
        // radios use string (e.g. "0", "1")
        const valueStr = String(value ?? '');
        this.selectedValues.set(optionId, valueStr);
        const valueRadios = this.radioElements.get(optionId);
        if (valueRadios) {
            const radio = valueRadios.get(valueStr);
            if (radio) {
                radio.checked = true;
            }
        }
    }

    reset() {
        for (const valueRadios of this.radioElements.values()) {
            for (const radio of valueRadios.values()) {
                radio.checked = false;
            }
        }
        this.selectedValues.clear();
        this.answered = false;
        this.onAnswerChange(this.questionNumber, false);
    }
}

export default QuizSurveyQuestion;
