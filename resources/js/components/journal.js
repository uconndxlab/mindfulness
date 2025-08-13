function initJournal() {
    console.log('Initializing journal...');
    const journalForm = document.getElementById('journalForm');
    if (!journalForm) return;

    const hasActivity = journalForm.dataset.hasActivity === 'true';
    const activityId = journalForm.dataset.activityId ? parseInt(journalForm.dataset.activityId, 10) : null;

    const noteInput = document.getElementById('note');
    const wordOtdInput = document.getElementById('topic');
    const wordOtdButton = document.getElementById('word-of-day');
    const submitBtn = document.getElementById('submitButton');
    
    const errDiv = document.getElementById('error-messages');
    const noteErrDiv = document.getElementById('error-messages-note');
    const wordErrDiv = document.getElementById('error-messages-word');
    const noteSuccessDiv = document.getElementById('success-message');

    journalForm.addEventListener('submit', function(event) {
        event.preventDefault(); 
        console.log('Submitting');
        submitNote();
    });
    
    // SUBMISSION
    function submitNote() {
        var body = null;
        if (hasActivity) {
            body = {
                note: noteInput.value.trim(),
                activity_id: activityId,
                activity: true
            }
        }
        else {
            body = {
                note: noteInput.value.trim(),
                topic: wordOtdInput.value,
                activity: false
            }
        }
        closeResponseMessages();
        console.log('Submitting note: ', body);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        return new Promise((resolve, reject) => {
            axios.post('/note', body, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => {
                console.log('Note submitted!');
                noteSuccessDiv.style.display = 'block';
                errDiv.style.display = 'none';
                if (hasActivity && window.activityComplete) {
                    window.activityComplete();
                }
                else {
                    wordOtdButton.innerHTML = 'Select a Topic'; 
                    wordOtdInput.value = 'no-topic';
                    // using global id reference like the original inline script
                    if (window.note) window.note.value = '';
                    unlockSubmit();
                }
                resolve(true);
            })
            .catch(error => {
                console.error('Error submitting form: ', error);
                if (error.response?.data?.errors) {
                    if (error.response.data.errors.note) {
                        noteErrDiv.textContent = error.response.data.errors.note.join(' ');
                        noteErrDiv.style.display = 'block';
                    }
                    if (error.response.data.errors.topic) {
                        wordErrDiv.textContent = error.response.data.errors.topic.join(' ');
                        wordErrDiv.style.display = 'block';
                    }
                } else {
                    const errorMessages = error.response?.data?.error_message || 'An unknown error occurred.';
                    errDiv.textContent = errorMessages;
                    errDiv.style.display = 'block';
                }
                reject(false);
            });
        });
    }

    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
            wordOtdButton.innerHTML = item.innerHTML; 
            wordOtdInput.value = item.value;
        });
    });

    // UNLOCK SUBMIT
    function unlockSubmit() {
        const noteValue = noteInput.value.trim();
        if (noteValue === '') {
            submitBtn.setAttribute('disabled', '');
        }
        else {
            submitBtn.removeAttribute('disabled');
        }
    }
    noteInput.addEventListener('input', function() {
        unlockSubmit();
    });

    function closeResponseMessages() {
        document.querySelectorAll('.note-err-message').forEach(msg => {
            if (msg.id != 'success-message') {
                msg.textContent = '';
            }
            msg.style.display = 'none';
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initJournal);
} else {
    initJournal();
}
