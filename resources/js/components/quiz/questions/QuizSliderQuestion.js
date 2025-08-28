/**
 * QuizSliderQuestion - Handles slider question logic with noUiSlider
 * Simple interface: isAnswered(), getValue(), setValue(), onAnswerChange(callback)
 */
class QuizSliderQuestion {
    constructor(questionDiv, questionNumber, onAnswerChange) {
        this.questionDiv = questionDiv;
        this.questionNumber = questionNumber;
        this.onAnswerChange = onAnswerChange; // Callback to notify controller
        this.sliderElement = null;
        this.hiddenInput = null;
        this.loadingElement = null;
        this.bubbleElement = null;
        this.questionData = null;
        this.currentValue = 50;
        this.isUserInteracting = false;
        
        this.init();
    }

    init() {
        this.findElements();
        this.extractQuestionData();
        this.initializeSlider();
        console.log(`Slider question ${this.questionNumber} initialized`);
    }

    findElements() {
        this.sliderElement = document.getElementById(`slider_${this.questionNumber}`);
        this.hiddenInput = document.getElementById(`slider_input_${this.questionNumber}`);
        this.loadingElement = document.getElementById(`slider_loading_${this.questionNumber}`);
        this.bubbleElement = document.getElementById(`quiz_slider_bubble_${this.questionNumber}`);
    }

    extractQuestionData() {
        const questionDataJson = this.questionDiv.getAttribute('data-question-json');
        this.questionData = questionDataJson ? JSON.parse(questionDataJson) : null;
        
        if (this.hiddenInput) {
            this.currentValue = parseInt(this.hiddenInput.value) || 50;
        }
    }

    initializeSlider() {
        if (!this.sliderElement || !window.noUiSlider) {
            console.warn(`Slider question ${this.questionNumber}: Missing slider element or noUiSlider library`);
            return;
        }

        // Get slider configuration from the new structure
        const sliderData = this.questionData?.slider_config || {};
        
        // Configure pips for responsive display
        let pipsConfig = undefined;
        if (sliderData.pips) {
            const pipKeys = Object.keys(sliderData.pips);
            const firstPipValue = pipKeys.length > 0 ? pipKeys[0] : null;
            const lastPipValue = pipKeys.length > 0 ? pipKeys[pipKeys.length - 1] : null;
            
            pipsConfig = {
                mode: 'values',
                values: pipKeys.map(Number),
                density: 4,
                format: {
                    to: (value) => {
                        if (window.innerWidth >= 768) return sliderData.pips[value];
                        if (value == firstPipValue || value == lastPipValue) return sliderData.pips[value];
                        return '';
                    }
                }
            };
        }

        // Create the slider
        window.noUiSlider.create(this.sliderElement, {
            start: [this.currentValue],
            connect: 'lower',
            step: sliderData.step ?? 1,
            range: {
                min: sliderData.min ?? 0,
                max: sliderData.max ?? 100
            },
            pips: pipsConfig
        });

        this.bindSliderEvents();
        this.hideLoadingShowSlider();
        
        // Sliders are always considered "answered" since they have a default value
        this.onAnswerChange(this.questionNumber, true);
    }

    bindSliderEvents() {
        if (!this.sliderElement.noUiSlider) return;
        
        this.sliderElement.noUiSlider.on('start', () => {
            this.isUserInteracting = true;
        });
        
        this.sliderElement.noUiSlider.on('update', (values, handle) => {
            const value = Math.round(values[handle]);
            this.currentValue = value;
            
            if (this.hiddenInput) {
                this.hiddenInput.value = value;
            }
            
            this.updateBubble(value, handle);
            
            // Notify controller of answer change
            this.onAnswerChange(this.questionNumber, true);
        });
        
        this.sliderElement.noUiSlider.on('end', () => {
            this.isUserInteracting = false;
            if (this.bubbleElement) {
                this.bubbleElement.classList.add('d-none');
            }
        });
    }

    updateBubble(value, handle) {
        if (!this.bubbleElement || !this.isUserInteracting) return;
        
        const sliderRect = this.sliderElement.getBoundingClientRect();
        const handles = this.sliderElement.querySelectorAll('.noUi-handle');
        const activeHandle = handles[handle];
        
        if (activeHandle) {
            const handleRect = activeHandle.getBoundingClientRect();
            const left = handleRect.left + handleRect.width / 2 - sliderRect.left;
            
            this.bubbleElement.style.transform = `translateX(${left}px) translateX(-50%)`;
            this.bubbleElement.textContent = value + '%';
            this.bubbleElement.classList.remove('d-none');
        }
    }

    hideLoadingShowSlider() {
        if (this.loadingElement) {
            this.loadingElement.classList.add('d-none');
        }
        
        if (this.sliderElement) {
            this.sliderElement.classList.remove('d-none');
        }
    }

    // Interface methods that controller can use
    isAnswered() {
        // Sliders are always considered answered since they have a default value
        return true;
    }

    getValue() {
        return this.currentValue;
    }

    // Method to set answer programmatically (for loading saved answers)
    setValue(value) {
        const numericValue = parseInt(value) || 50;
        this.currentValue = numericValue;
        
        if (this.hiddenInput) {
            this.hiddenInput.value = numericValue;
        }
        
        if (this.sliderElement && this.sliderElement.noUiSlider) {
            this.sliderElement.noUiSlider.set([numericValue]);
        }
        
        // Notify controller
        this.onAnswerChange(this.questionNumber, true);
        
        console.log(`Slider question ${this.questionNumber} value set to:`, numericValue);
    }

    // Reset the question to default value
    reset() {
        const defaultValue = this.questionData?.slider_config?.default ?? 50;
        this.setValue(defaultValue);
    }
}

export default QuizSliderQuestion;
