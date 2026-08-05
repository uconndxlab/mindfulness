class QuizSurveyQuestion {
    static MAX_NOTE_LENGTH = 3000;

    constructor(questionDiv, questionNumber, onAnswerChange, initialAverage = null) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange; // callback
        this.average = initialAverage;

        this.radioElements = new Map();   // optionId -> (value -> radio element)
        this.selectedValues = new Map();  // optionId -> selected value (string)
        this.inverseOptions = new Map();  // optionId -> boolean (inverse scoring)
        this.noteValues = new Map();      // optionId -> note text
        this.openEndedOptionIds = new Set();
        this.openEndedElements = new Map(); // optionId -> textarea element
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

            if (option.open_ended_config) {
                this.openEndedOptionIds.add(optionId);
                const textarea = document.getElementById(`survey_open_ended_${this.questionNumber}_${optionId}`);
                if (textarea) {
                    this.openEndedElements.set(optionId, textarea);
                    textarea.maxLength = QuizSurveyQuestion.MAX_NOTE_LENGTH;
                    textarea.addEventListener('input', () => this.handleNoteInput(optionId));
                }
            }

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
        this.clearRowValidationError(optionId);
        if (this.getUnansweredOptionIds().length === 0) {
            this.updateAverage();
        }
        const nowAnswered = this.isAnswered();
        if (nowAnswered) {
            this.clearValidationErrors();
        }
        this.onAnswerChange(this.questionNumber, nowAnswered);
        console.log(`Survey question ${this.questionNumber} option ${optionId} answered:`, radio.value);
    }

    handleNoteInput(optionId) {
        const textarea = this.openEndedElements.get(optionId);
        const value = textarea?.value ?? '';
        this.noteValues.set(optionId, value);
        this.clearRowValidationError(optionId);

        const nowAnswered = this.isAnswered();
        if (nowAnswered) {
            this.clearValidationErrors();
        }
        this.onAnswerChange(this.questionNumber, nowAnswered);
    }

    getUnansweredOptionIds() {
        const options = this.questionData?.options || [];
        return options
            .filter((option) => !this.selectedValues.has(option.id))
            .map((option) => option.id);
    }

    getMissingNoteOptionIds() {
        const missing = [];
        for (const optionId of this.openEndedOptionIds) {
            const note = this.noteValues.get(optionId)?.trim() ?? '';
            if (note.length === 0) {
                missing.push(optionId);
            }
        }
        return missing;
    }

    getInvalidOptionIds() {
        const unanswered = this.getUnansweredOptionIds();
        const missingNotes = this.getMissingNoteOptionIds();
        const combined = [...unanswered];
        for (const optionId of missingNotes) {
            if (!combined.includes(optionId)) {
                combined.push(optionId);
            }
        }
        return combined;
    }

    clearRowValidationError(optionId) {
        const field = document.getElementById(`survey_field_${this.questionNumber}_${optionId}`);
        field?.classList.remove('is-invalid', 'is-invalid-rating', 'is-invalid-note');
    }

    clearValidationErrors() {
        this.questionDiv.querySelectorAll('.quiz-survey-field.is-invalid, .quiz-survey-field.is-invalid-rating, .quiz-survey-field.is-invalid-note').forEach((field) => {
            field.classList.remove('is-invalid', 'is-invalid-rating', 'is-invalid-note');
        });
        const alert = document.getElementById(`survey_validation_alert_${this.questionNumber}`);
        alert?.classList.add('d-none');
    }

    validateForSubmit() {
        const invalid = this.getInvalidOptionIds();

        if (invalid.length === 0) {
            return {
                valid: true,
                unansweredCount: 0,
                firstUnansweredOptionId: null,
            };
        }

        for (const optionId of invalid) {
            const field = document.getElementById(`survey_field_${this.questionNumber}_${optionId}`);
            field?.classList.add('is-invalid');
            if (!this.selectedValues.has(optionId)) {
                field?.classList.add('is-invalid-rating');
            }
            if (this.openEndedOptionIds.has(optionId)) {
                const note = this.noteValues.get(optionId)?.trim() ?? '';
                if (note.length === 0) {
                    field?.classList.add('is-invalid-note');
                }
            }
        }

        const alert = document.getElementById(`survey_validation_alert_${this.questionNumber}`);
        alert?.classList.remove('d-none');

        return {
            valid: false,
            unansweredCount: invalid.length,
            firstUnansweredOptionId: invalid[0],
        };
    }

    updateAverage() {
        let total = 0;
        let count = 0;
        let range = this.scaleMax - this.scaleMin;

        for (const [optionId, value] of this.selectedValues) {
            let score = parseInt(value, 10) || 0;
            if (this.inverseOptions.get(optionId)) {
                score = this.scaleMax + this.scaleMin - score;
            }
            // convert to percentage
            score = ((score - this.scaleMin) / range) * 100;
            total += score;
            count++;
        }

        if (count > 0) {
            this.average = (total / count).toFixed(2);
        }
    }

    // interface methods for QuizController
    isAnswered() {
        const options = this.questionData?.options || [];
        if (options.length === 0) return false;
        for (const option of options) {
            if (!this.selectedValues.has(option.id)) return false;
        }
        for (const optionId of this.openEndedOptionIds) {
            const note = this.noteValues.get(optionId)?.trim() ?? '';
            if (note.length === 0) return false;
        }
        return true;
    }

    getAverage() {
        if (this.selectedValues.size > 0) {
            this.updateAverage();
        }
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

    getNotes() {
        if (this.openEndedOptionIds.size === 0) {
            return null;
        }

        const result = [];
        for (const optionId of this.openEndedOptionIds) {
            const note = this.noteValues.get(optionId)?.trim() ?? '';
            if (note.length > 0) {
                result.push({ [optionId]: note });
            }
        }

        return result.length > 0 ? result : null;
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

    setNotes(notesArray) {
        if (!Array.isArray(notesArray)) return;

        for (const item of notesArray) {
            const optionId = parseInt(Object.keys(item)[0], 10);
            const note = Object.values(item)[0];
            const trimmed = typeof note === 'string' ? note.trim() : '';
            this.noteValues.set(optionId, trimmed);
            const textarea = this.openEndedElements.get(optionId);
            if (textarea) {
                textarea.value = trimmed;
            }
        }

        this.answered = this.isAnswered();
        console.log(`Survey question ${this.questionNumber} notes set to:`, JSON.stringify(notesArray));
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
        for (const textarea of this.openEndedElements.values()) {
            textarea.value = '';
        }
        this.noteValues.clear();
        this.answered = false;
        this.onAnswerChange(this.questionNumber, false);
    }
}

export default QuizSurveyQuestion;
