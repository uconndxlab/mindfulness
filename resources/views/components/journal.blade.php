@if (isset($journal))
    <form id="journalForm" method="POST" class="pt-3" data-has-activity="{{ isset($journal->activity) ? 'true' : 'false' }}" data-activity-id="{{ $journal->activity ? $journal->activity->id : '' }}">
        <p><em>For your data safety, please don’t write any sensitive personal information here such as your home address, government ID numbers, or financial details.</em></p>
        @csrf
        <div id="success-message" class="alert alert-success note-err-message d-none">
            Journal saved!
            @if (isset($journal->activity))
                <a href="{{ route('journal.library', ['activity' => $journal->activity_id]) }}">Click here to view past journals<i class="bi bi-arrow-right"></i></a>
            @endif
        </div>
        <div id="error-messages" class="alert alert-danger d-none"></div>
        @if ($journal->prompts)
            <div class="text-left mb-3">
                <div class="journal-prompts">
                    @markdown($journal->prompts)
                </div>
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
            <div id="error-messages-word" class="text-danger note-err-message d-none"></div>
        @endif
        <textarea class="form-control" id="note" name="note" rows="5">{{ $journal->answer }}</textarea>
        <div id="error-messages-note" class="text-danger note-err-message d-none"></div>
        <div class="d-flex justify-content-between">
            <button type="submit" id="submitButton" class="btn-quiz ms-auto" {{ $journal->answer ? '' : 'disabled'}}>
                Save Journal <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </form>
@endif