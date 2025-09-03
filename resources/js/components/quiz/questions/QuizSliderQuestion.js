class QuizSliderQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange; // callback
        
        // maps for efficient lookups across multiple sliders
        this.sliders = new Map();           // optionId -> noUiSlider instance
        this.hiddenInputs = new Map();      // optionId -> input element  
        this.currentValues = new Map();     // optionId -> current value
        this.loadingElements = new Map();   // optionId -> loading element
        this.bubbleElements = new Map();    // optionId -> bubble element
        this.interactionStates = new Map(); // optionId -> boolean (is user currently interacting)
        this.userInteracted = new Map();    // optionId -> boolean (has user ever interacted)

        this.answered = false;
        
        const questionDataJson = this.questionDiv.getAttribute('data-question-json');
        this.questionData = questionDataJson ? JSON.parse(questionDataJson) : null;
        
        this.batchInit();
    }

    batchInit() {
        console.log(`Slider question ${this.questionNumber} batchInit`);
        this.findAndCacheElements();
        this.initializeAllSliders();
        console.log(`Slider question ${this.questionNumber} initialized with ${this.sliders.size} slider(s)`);
    }

    findAndCacheElements() {
        // find all elements based on the options structure
        const options = this.questionData?.options || [];
        
        for (const option of options) {
            const optionId = option.id;
            
            // cache elements for this option
            const sliderElement = document.getElementById(`slider_${this.questionNumber}_${optionId}`);
            const hiddenInput = document.getElementById(`slider_input_${this.questionNumber}_${optionId}`);
            const loadingElement = document.getElementById(`slider_loading_${this.questionNumber}_${optionId}`);
            const bubbleElement = document.getElementById(`quiz_slider_bubble_${this.questionNumber}_${optionId}`);
            
            if (sliderElement) {
                this.sliders.set(optionId, sliderElement);
                this.hiddenInputs.set(optionId, hiddenInput);
                this.loadingElements.set(optionId, loadingElement);
                this.bubbleElements.set(optionId, bubbleElement);
                this.interactionStates.set(optionId, false);
                this.userInteracted.set(optionId, false);
                
                // get current value from hidden input
                const currentValue = hiddenInput ? (parseInt(hiddenInput.value) || 50) : 50;
                this.currentValues.set(optionId, currentValue);
            }
        }
    }

    initializeAllSliders() {
        if (!window.noUiSlider) {
            console.warn(`Slider question ${this.questionNumber}: noUiSlider library not available`);
            return;
        }

        const options = this.questionData?.options || [];
        
        for (const option of options) {
            const optionId = option.id;
            const sliderElement = this.sliders.get(optionId);
            
            if (!sliderElement) {
                console.warn(`Slider question ${this.questionNumber}: Missing slider element for option ${optionId}`);
                continue;
            }

            this.initializeSingleSlider(optionId, option);
        }
    }

    initializeSingleSlider(optionId, option) {
        const sliderElement = this.sliders.get(optionId);
        const currentValue = this.currentValues.get(optionId);
        const sliderConfig = option.slider_config || {};
        
        let pipsConfig = undefined;
        if (sliderConfig.pips) {
            const pipKeys = Object.keys(sliderConfig.pips);
            const firstPipValue = pipKeys.length > 0 ? pipKeys[0] : null;
            const lastPipValue = pipKeys.length > 0 ? pipKeys[pipKeys.length - 1] : null;
            
            pipsConfig = {
                mode: 'values',
                values: pipKeys.map(Number),
                density: 4,
                format: {
                    to: (value) => {
                        if (window.innerWidth >= 768) return sliderConfig.pips[value];
                        if (value == firstPipValue || value == lastPipValue) return sliderConfig.pips[value];
                        return '';
                    }
                }
            };
        }

        // create the slider
        window.noUiSlider.create(sliderElement, {
            start: [currentValue],
            connect: 'lower',
            step: sliderConfig.step ?? 1,
            range: {
                min: sliderConfig.min ?? 0,
                max: sliderConfig.max ?? 100
            },
            pips: pipsConfig
        });

        this.bindSliderEvents(optionId);
        this.hideLoadingShowSlider(optionId);
    }

    bindSliderEvents(optionId) {
        const sliderElement = this.sliders.get(optionId);
        const hiddenInput = this.hiddenInputs.get(optionId);
        const bubbleElement = this.bubbleElements.get(optionId);
        
        if (!sliderElement || !sliderElement.noUiSlider) return;
        
        sliderElement.noUiSlider.on('start', () => {
            this.interactionStates.set(optionId, true);
            // mark this slider as interacted with
            this.userInteracted.set(optionId, true);
            this.checkIfAllSlidersInteracted();
        });
        
        sliderElement.noUiSlider.on('update', (values, handle) => {
            const value = Math.round(values[handle]);
            this.currentValues.set(optionId, value);
            
            if (hiddenInput) {
                hiddenInput.value = value;
            }
            
            this.updateBubble(optionId, value, handle);
        });
        
        sliderElement.noUiSlider.on('end', () => {
            this.interactionStates.set(optionId, false);
            if (bubbleElement) {
                bubbleElement.classList.add('d-none');
            }
        });
    }

    checkIfAllSlidersInteracted() {
        // check if all sliders have been interacted with at least once
        let allInteracted = true;
        for (const [optionId, hasInteracted] of this.userInteracted) {
            if (!hasInteracted) {
                allInteracted = false;
                break;
            }
        }
        
        if (allInteracted && !this.answered) {
            this.answered = true;
            this.onAnswerChange(this.questionNumber, true);
            console.log(`Slider question ${this.questionNumber} marked as answered - all sliders interacted`);
        }
    }

    updateBubble(optionId, value, handle) {
        const bubbleElement = this.bubbleElements.get(optionId);
        const sliderElement = this.sliders.get(optionId);
        const isInteracting = this.interactionStates.get(optionId);
        
        if (!bubbleElement || !sliderElement || !isInteracting) return;
        
        const sliderRect = sliderElement.getBoundingClientRect();
        const handles = sliderElement.querySelectorAll('.noUi-handle');
        const activeHandle = handles[handle];
        
        if (activeHandle) {
            const handleRect = activeHandle.getBoundingClientRect();
            const left = handleRect.left + handleRect.width / 2 - sliderRect.left;
            
            bubbleElement.style.transform = `translateX(${left}px) translateX(-50%)`;
            bubbleElement.textContent = value + '%';
            bubbleElement.classList.remove('d-none');
        }
    }

    hideLoadingShowSlider(optionId) {
        const loadingElement = this.loadingElements.get(optionId);
        const sliderElement = this.sliders.get(optionId);
        
        if (loadingElement) {
            loadingElement.classList.add('d-none');
        }
        if (sliderElement) {
            sliderElement.classList.remove('d-none');
        }
    }

    // interface methods for QuizController
    isAnswered() {
        // sliders always considered answered...
        return this.answered;
    }

    getValue() {
        // return array format: [{"0": 67}, {"1": 42}] 
        const result = [];
        for (const [optionId, value] of this.currentValues) {
            const item = {};
            item[optionId] = value;
            result.push(item);
        }
        return result;
    }

    // load saved answers
    setValue(values) {
        // handle array format: [{"0": 67}, {"1": 42}]
        if (Array.isArray(values)) {
            for (const item of values) {
                const optionId = parseInt(Object.keys(item)[0]);
                const value = Object.values(item)[0];
                this.setSingleSliderValue(optionId, value);
                // mark as interacted since we're loading a saved answer
                this.userInteracted.set(optionId, true);
            }
            // check if all sliders are now considered interacted
            this.checkIfAllSlidersInteracted();
        }
        
        console.log(`Slider question ${this.questionNumber} values set to:`, JSON.stringify(values));
    }

    setSingleSliderValue(optionId, value) {
        const numericValue = parseInt(value) || 50;
        this.currentValues.set(optionId, numericValue);
        
        const hiddenInput = this.hiddenInputs.get(optionId);
        if (hiddenInput) {
            hiddenInput.value = numericValue;
        }
        
        const sliderElement = this.sliders.get(optionId);
        if (sliderElement && sliderElement.noUiSlider) {
            sliderElement.noUiSlider.set([numericValue]);
        }
    }

    reset() {
        const options = this.questionData?.options || [];
        
        for (const option of options) {
            const defaultValue = option.slider_config?.default ?? 50;
            this.setSingleSliderValue(option.id, defaultValue);
            // reset interaction tracking
            this.userInteracted.set(option.id, false);
        }
        
        // reset answered state
        this.answered = false;
        this.onAnswerChange(this.questionNumber, false);
    }
}

export default QuizSliderQuestion;
