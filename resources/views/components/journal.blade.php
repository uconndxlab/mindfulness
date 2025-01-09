@if (isset($journal))
    <form id="journalForm" method="POST" class="pt-3">
        <p>***Please do not write any sensitive information here.***</p>
        @csrf
        <div id="success-message" class="alert alert-success note-err-message" style="display: none;">
            Journal saved!
            @if (isset($journal->activity))
                <a href="{{ route('journal.library', ['activity' => $journal->activity_id]) }}">Click here to view past journals<i class="bi bi-arrow-right"></i></a>
            @endif
        </div>
        <div id="error-messages" class="alert alert-danger" style="display: none;"></div>
        @if ($journal->prompts)
            <div class="text-left mb-3">
                <h4>{!! $journal->prompts !!}</h4>
            </div>
        @else
            <div class="form-group dropdown">
                <label class="fw-bold col-12" for="word_dropdown">Please select what you want to talk about in this journal (optional):</label>
                <button id="word-of-day" class="btn btn-xlight dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Select a Topic
                </button>
                <ul class="dropdown-menu" id="word_dropdown" name="word_dropdown">
                    <li><button class="dropdown-item" type="button" value="self-care">Self-care</button></li>
                    <li><button class="dropdown-item" type="button" value="self-understanding">Self-understanding</button></li>
                    <li><button class="dropdown-item" type="button" value="parenting">Parenting</button></li>
                    <li><button class="dropdown-item" type="button" value="gratitude">Gratitude</button></li>
                    <li><button class="dropdown-item" type="button" value="joy">Joy</button></li>
                    <li><button class="dropdown-item" type="button" value="love">Love</button></li>
                    <li><button class="dropdown-item" type="button" value="relationships">Relationships</button></li>
                    <li><button class="dropdown-item" type="button" value="boundaries">Boundaries</button></li>
                    <li><button class="dropdown-item" type="button" value="no-topic">No Topic</button></li>
                </ul>
                <input type="hidden" name="topic" id="topic" value="no-topic">
            </div>
            <div id="error-messages-word" class="text-danger note-err-message" style="display: none;"></div>
        @endif
        <textarea class="form-control" id="note" name="note" rows="5">{{ $journal->answer }}</textarea>
        <div id="error-messages-note" class="text-danger note-err-message" style="display: none;"></div>
        <div class="d-flex justify-content-between">
            <button type="submit" id="submitButton" class="btn-quiz ms-auto" {{ $journal->answer ? '' : 'disabled'}}>
                Save Journal <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Initializing journal...');
            const hasActivity = {{ isset($journal->activity) ? 'true' : 'false' }};
            
            const journalForm = document.getElementById('journalForm');
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
            
            //SUBMISSION
            function submitNote() {
                var body = null;
                if (hasActivity) {
                    body = {
                        note: noteInput.value.trim(),
                        activity_id: {{ $journal->activity ? $journal->activity->id : 'false' }},
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
                return new Promise((resolve, reject) => {
                    axios.post('/note', body, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        console.log('Note submitted!');
                        noteSuccessDiv.style.display = 'block';
                        errDiv.style.display = 'none';
                        if (hasActivity) {
                            activityComplete();
                        }
                        else {
                            wordOtdButton.innerHTML = 'Select a Topic'; 
                            wordOtdInput.value = 'no-topic';
                            note.value = '';
                            unlockSubmit();
                        }
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
                            if (error.response.data.errors.topic) {
                                wordErrDiv.textContent = error.response.data.errors.topic.join(' ');
                                wordErrDiv.style.display = 'block';
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

            //dropdown functionality
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function() {
                    wordOtdButton.innerHTML = item.innerHTML; 
                    wordOtdInput.value = item.value;
                });
            });

            //UNLOCK SUBMIT
            function unlockSubmit() {
                const noteValue = noteInput.value.trim();
                if (noteValue === '') {
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
                    if (msg.id != 'success-message') {
                        msg.textContent = '';
                    }
                    msg.style.display = 'none';
                });
            }
        });
    </script>
@endif