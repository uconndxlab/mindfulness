@props(['reflection'])

@php
    $questionsWithAnswers = \App\Services\QuizAnswerFormatter::getQuestionsWithAnswers($reflection);
@endphp

@if($reflection)
    <div class="questions-answers">
        @forelse($questionsWithAnswers as $qa)
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Question {{ $qa['number'] }}</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        @markdown($qa['question'])
                    </div>
                    <div class="answer-section">
                        <h6 class="text-muted">Answer:</h6>
                        @if($qa['type'] === 'slider' || $qa['type'] === 'survey')
                            @foreach($qa['formatted_answer']['items'] as $item)
                                <div class="mb-3 p-3 bg-light rounded">
                                    <div class="mb-2">@markdown($item['text'])</div>
                                    <div class="d-flex align-items-center justify-content-center">
                                        <span class="stat-value">{{ $item['value'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        @elseif($qa['type'] === 'checkbox' || $qa['type'] === 'radio')
                            <ul class="list-group">
                                @foreach($qa['formatted_answer']['items'] as $item)
                                    <li class="list-group-item d-flex align-items-start">
                                        <i class="bi bi-check-circle-fill text-success me-2 flex-shrink-0"></i>
                                        <span class="flex-grow-1">@markdown($item['text'])</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="alert alert-secondary">
                                {{ $qa['formatted_answer']['display'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-warning">
                No questions found for this reflection.
            </div>
        @endforelse
    </div>
@else
    <div class="alert alert-danger">
        Unable to load reflection data.
    </div>
@endif
