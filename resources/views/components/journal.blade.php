@if (isset($journal))
    <form id="journalForm" method="POST" class="pt-3">
        @csrf
        <div class="text-left mb-3">
            <h4>{{ $journal->prompts }}</h4>
        </div>
        <textarea class="form-control" id="note" name="note" rows="5">{{ $journal->answer }}</textarea>
        <div id="error-messages-note" class="text-danger note-err-message" style="display: none;"></div>
        <div class="d-flex justify-content-between">
            <button type="submit" id="submitButton" class="btn-quiz ms-auto" {{ $journal->answer ? '' : 'disabled'}}>
                Submit <i class="bi bi-arrow-up"></i>
            </button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing journal...');
            const journalId = {{ $journal->id }};
    
            const journalForm = document.getElementById('journalForm');
            const noteInput = document.getElementById('note');
            const submitBtn = document.getElementById('submitButton');

            const noteErrDiv = document.getElementById('error-messages-note');

            journalForm.addEventListener('submit', function(event) {
                event.preventDefault(); 
                console.log('Submitting');
                submitNote();
            });

            //SUBMISSION
            function submitNote() {
                closeResponseMessages();
                return new Promise((resolve, reject) => {
                    axios.post('/note', {
                        note: noteInput.value.trim(),
                        activity_id: {{ $journal->activity->id }},
                        activity: true
                    }, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        console.log('Note submitted!');
                        activityComplete();
                        resolve(true);
                    })
                    .catch(error => {
                        console.error('Error submitting form: ', error);
                        //display error
                        if (error.response?.data?.errors) {
                            if (error.response.data.errors.note) {
                                noteErrDiv.textContent = error.response.data.errors.note.join(' ');
                                noteErrDiv.style.display = 'block';
                            }
                        } else {
                            //other errors
                            const errorMessages = error.response?.data?.error_message || 'An unknown error occurred.';
                            errDiv.textContent = errorMessages;
                            errDiv.style.display = 'block';
                        }
                        reject(false);
                    });
                });
            }


            //UNLOCK SUBMIT
            function unlockSubmit() {
                const value = noteInput.value.trim();
                if (value === '') {
                    submitBtn.setAttribute('disabled', '');
                }
                else {
                    submitBtn.removeAttribute('disabled');
                }
            }
            //call on input
            noteInput.addEventListener('input', function() {
                unlockSubmit();
            });

            function closeResponseMessages() {
                document.querySelectorAll('.note-err-message').forEach(msg => {
                    msg.textContent = '';
                    msg.style.display = 'none';
                });
            }
        });
    </script>
@endif