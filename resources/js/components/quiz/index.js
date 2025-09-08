import QuizController from './QuizController.js';

function initQuiz() {
    console.log('Initializing new quiz system...');
    const quizController = new QuizController();
    window.quizController = quizController;
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQuiz);
} else {
    initQuiz();
}

export { initQuiz };
