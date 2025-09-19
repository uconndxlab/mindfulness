@if (!isset($notes) || $notes->isEmpty())
    <div class="text-left muted">
        <span>Continue progressing to find a Journal activity, or write your first journal in the <a href="/journal">Journal</a> tab.</span>
    </div>
@else
    @foreach ($notes as $index => $note)
        <div class="prior-note">
            <div class="top-note">
                <h5 class="fw-bold d-flex justify-content-between">
                    @if ($note->topic === 'no-topic')
                        <span>No Topic</span>
                    @else
                        <div class="journal-labels">
                            @markdown(ucfirst(strip_tags($note->topic)))
                        </div>
                    @endif
                </h5>
                <small>{{ $note->formatted_date }}</small>
            </div>
            @if (strlen($note->note) > 100)
                <div id="note_content_{{ $index }}" class="note-content-extra">
                    <p class="note-content">
                        {{ substr($note->note, 0, 75) }}
                        <span class="dots">
                            ...
                        </span>
                        <span class="more-text d-none">
                            {{ substr($note->note, 75) }}
                        </span>
                    </p>
                    <button id="read_more_{{ $index }}" type="button" class="btn btn-link read-more-btn">Read More...</button>
                </div>
            @else
                <p id="note_content_{{ $index }}" class="note-content">
                    {{ $note->note }}
                </p>
            @endif
        </div>
    @endforeach
    <div>
        {{ $notes->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
@endif