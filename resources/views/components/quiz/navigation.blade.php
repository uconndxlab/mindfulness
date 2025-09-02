<div class="d-flex justify-content-between quiz-nav-container">
    <button id="prev_q_button" type="button" class="btn-quiz invisible" disabled>
        <i class="bi bi-arrow-left"></i> Previous 
    </button>
    <button id="next_q_button" type="button" class="btn-quiz {{ $hasMultipleQuestions ? '' : 'd-none' }}" disabled>
        Next <i class="bi bi-arrow-right"></i>
    </button>
    <button type="submit" id="submitButton" class="btn btn-primary ms-auto {{ $hasMultipleQuestions ? 'd-none' : '' }} mt-2 mb-0" disabled>
        Submit <i class="bi bi-arrow-right"></i>
    </button>
</div>
