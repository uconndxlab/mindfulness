import QuizRadioQuestion from './questions/QuizRadioQuestion.js';
import QuizCheckboxQuestion from './questions/QuizCheckboxQuestion.js';
import QuizSliderQuestion from './questions/QuizSliderQuestion.js';

/**
 * QuizController - handle quiz logic and navigation
 * Question components handle their own logic
 */
class QuizController {
    constructor() {
        this.quizForm = document.getElementById('quizForm');
        if (!this.quizForm) return;

        console.log('Initializing quiz...');
        
        this.questionNumber = 1;
        this.quizId = parseInt(this.quizForm.getAttribute('data-quiz-id') || '0', 10);
        this.questionCount = this.quizForm.querySelectorAll('.quiz-div').length;
        this.answerSet = new Set();
        const answersJson = this.quizForm.getAttribute('data-answers');
        this.answers = answersJson ? JSON.parse(answersJson) : {};
        this.average = parseFloat(this.quizForm.getAttribute('data-average')) || null;

        this.quizContainer = document.getElementById('quiz-container');

        // nav buttons
        this.prevQBtn = document.getElementById('prev_q_button');
        this.nextQBtn = document.getElementById('next_q_button');
        this.submitBtn = document.getElementById('submitButton');
        
        // map qs to components
        this.questionComponents = new Map();

        this.initializeOptimized();
    }

    // efficient initialization
    async initializeOptimized() {
        try {
            // batch DOM operations to prevent thrashing
            this.batchInitialization();
            this.initializeEventListeners();
            // initialize components (slider setup is async)
            await this.initializeQuestionComponents();
            this.populateAnswers(this.answers);
            this.updateNavigationButtons();
            this.markAsLoaded();
            
        } catch (error) {
            console.error('Quiz initialization failed:', error);
            // fallback - still show quiz
            this.markAsLoaded();
        }
    }

    batchInitialization() {
        // pre-cache all question elements to avoid repeated DOM queries
        const quizDivs = this.quizForm.querySelectorAll('.quiz-div');
        this.questionElements = new Map();
        
        quizDivs.forEach(div => {
            const questionNumber = parseInt(div.getAttribute('data-number'));
            this.questionElements.set(questionNumber, div);
        });
    }

    initializeEventListeners() {
        // form and nav
        this.quizForm.addEventListener('submit', (event) => {
            event.preventDefault();
            console.log('submitting');
            this.submitAnswers();
        });

        if (this.prevQBtn) this.prevQBtn.addEventListener('click', () => { 
            this.changeQuestion(this.questionNumber - 1); 
        });
        if (this.nextQBtn) this.nextQBtn.addEventListener('click', () => { 
            this.changeQuestion(this.questionNumber + 1); 
        });
    }

    initializeQuestionComponents() {
        for (const [questionNumber, questionDiv] of this.questionElements) {
            const questionType = questionDiv.getAttribute('data-type');
            
            let questionComponent;
            
            // init component based on type
            if (questionType === 'radio') {
                questionComponent = new QuizRadioQuestion(
                    questionDiv, 
                    questionNumber, 
                    (qNum, isAnswered) => this.onQuestionAnswerChange(qNum, isAnswered)
                );
            } else if (questionType === 'checkbox') {
                questionComponent = new QuizCheckboxQuestion(
                    questionDiv, 
                    questionNumber, 
                    (qNum, isAnswered) => this.onQuestionAnswerChange(qNum, isAnswered)
                );
            } else if (questionType === 'slider') {
                questionComponent = new QuizSliderQuestion(
                    questionDiv, 
                    questionNumber, 
                    (qNum, isAnswered) => this.onQuestionAnswerChange(qNum, isAnswered),
                    this.average
                );
            }
            
            if (questionComponent) {
                // add to map
                this.questionComponents.set(questionNumber, questionComponent);
                console.log(`Initialized ${questionType} question ${questionNumber}`);
            }
        };
    }

    populateAnswers(answers) {
        // answer format = { "1": [{"1": null}, {"6": "other text"}], "2": [{"3": null}], "3": [92], "4": [0] }
        console.log(`Populating form with answers: ${answers}`);
        for (const [questionNumber, answerArray] of Object.entries(answers)) {
            const questionComponent = this.questionComponents.get(parseInt(questionNumber));
            
            if (questionComponent) {
                // component knows how to handle its own answers
                questionComponent.setValue(answerArray);
            }
        }
    }
    
    markAsLoaded() {
        if (this.quizContainer) {
            // use requestAnimationFrame to ensure smooth transition
            requestAnimationFrame(() => {
                this.quizContainer.classList.remove('quiz-loading');
                this.quizContainer.classList.add('quiz-loaded');
                
                // initialize popovers for practice quality info icons
                this.initializePopovers();
            });
        }
    }
    
    initializePopovers() {
        // initialize Bootstrap popovers for info icons
        const popoverTriggerList = [].slice.call(this.quizForm.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function (popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    // callback for answer change
    onQuestionAnswerChange(questionNumber, isAnswered) {
        console.log(`Question ${questionNumber} answer changed. Answered: ${isAnswered}`);
        
        // update nav buttons if on current question - should always be the case
        if (questionNumber === this.questionNumber) {
            // nav checks for answer again anyway
            this.updateNavigationButtons();
        }
    }

    // check if current question is answered
    isCurrentQuestionAnswered() {
        const questionComponent = this.questionComponents.get(this.questionNumber);
        return questionComponent ? questionComponent.isAnswered() : false;
    }

    // update nav based on current question state
    updateNavigationButtons() {
        const isAnswered = this.isCurrentQuestionAnswered();
        const isFirstQuestion = this.questionNumber === 1;
        const isLastQuestion = this.questionNumber === this.questionCount;

        if (isFirstQuestion) {
            this.prevQBtn?.classList.add('invisible');
            this.prevQBtn?.setAttribute('disabled', '');
        }
        else {
            this.prevQBtn?.removeAttribute('disabled', '');
            this.prevQBtn?.classList.remove('invisible');
        }

        if (isLastQuestion) {
            this.nextQBtn?.classList.add('d-none');
            this.submitBtn?.classList.remove('d-none');
        }
        else {
            this.submitBtn?.classList.add('d-none');
            this.nextQBtn?.classList.remove('d-none');
        }

        const endBtn = isLastQuestion ? this.submitBtn : this.nextQBtn;
        if (isAnswered) {
            endBtn?.removeAttribute('disabled');
        }
        else {
            endBtn?.setAttribute('disabled', '');
        }
    }
    
    changeQuestion(q_no) {
        console.log('Question No.: ' + q_no);
        this.questionNumber = q_no;

        if (window.pauseAllAudio) window.pauseAllAudio();

        for (const [questionNumber, questionDiv] of this.questionElements) {
            if (questionNumber === this.questionNumber) {
                questionDiv.classList.remove('d-none');
            } else {
                questionDiv.classList.add('d-none');
            }
        }

        this.updateNavigationButtons();
    }

    submitAnswers() {
        const newFormatAnswers = this.collectAnswers();
        if (this.submitBtn) {
            this.submitBtn.blur();
        }
        
        // create formdata
        const formData = new FormData();
        formData.append('quiz_id', this.quizId);
        formData.append('answers', JSON.stringify(newFormatAnswers.answers));
        
        // if there's an average from slider question, include it
        if (newFormatAnswers.average !== null && newFormatAnswers.average !== undefined) {
            formData.append('average', newFormatAnswers.average);
        }
        
        return new Promise((resolve, reject) => {
            window.axios.post('/quiz/' + this.quizId, formData)
                .then(response => {
                    document.dispatchEvent(new CustomEvent('activity:complete', {
                        detail: {
                            message: true,
                            voice: null
                        }
                    }));
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

    collectAnswers() {
        const answers = {};
        let average = null;
        
        // get answers from each component
        for (const [questionNumber, component] of this.questionComponents) {
            if (component.isAnswered()) {
                answers[questionNumber] = component.getValue();
                
                // if this is a slider question, get its average
                if (typeof component.getAverage === 'function') {
                    average = component.getAverage();
                }
            }
        }
        
        return { answers, average };
    }
}

export default QuizController;
