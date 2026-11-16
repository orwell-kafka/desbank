@if (! empty($modal))
    <p class="work-intro work-picker-intro">Pick another <strong>{{ $question->bloomLevel }}</strong> / <strong>{{ $question->questionType }}</strong> question from the question bank. Only questions that keep the Table of Specifications intact are listed.</p>
@endif
@php $letters = ['A','B','C','D','E','F','G','H','I','J','K','L']; @endphp
            <section class="work-directory" style="margin-top:29px;" aria-labelledby="current-q">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">CURRENT QUESTION</p><h2 id="current-q">Being replaced</h2></div>
                </div>
                <div style="padding:18px 29px 22px;">
                    <p class="work-q-text">{{ $question->content }}</p>
                    @if ($question->choices)
                        <ul class="work-choices" aria-label="Answer choices">
                            @foreach (explode(',', $question->choices) as $i => $choice)
                                <li><b>{{ $letters[$i] ?? '' }}.</b><span>{{ trim($choice) }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                    <div style="margin-top:10px;">
                        <span class="work-chip">{{ $question->bloomLevel }}</span>
                        <span class="work-chip work-chip-gold">{{ $question->questionType }}</span>
                    </div>
                </div>
            </section>

            <section class="work-directory" style="margin-top:0;" aria-labelledby="available-q">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">QUESTION BANK</p><h2 id="available-q">Available replacements</h2></div>
                    <span class="work-count" id="candidateCount">{{ $candidates->count() }} {{ $candidates->count() === 1 ? 'question' : 'questions' }}</span>
                </div>

                @if ($candidates->isEmpty())
                    <div class="work-empty-inline">
                        No other {{ $question->bloomLevel }} / {{ $question->questionType }} questions are available. Add more to the question bank, then come back.
                        <a class="work-row-link work-row-link-main" href="{{ route('courses.topics.index', $course) }}">Go to topics</a>
                    </div>
                @else
                    <div style="padding:16px 29px 0;">
                        <label class="work-label" for="candidateFilter">Search these questions</label>
                        <input type="search" id="candidateFilter" class="work-input" placeholder="Type a word from the question or topic" autocomplete="off">
                    </div>
                    <div class="work-table-scroll" role="region" aria-label="Available replacement questions" tabindex="0">
                        <table class="work-table" id="candidateTable">
                            <thead><tr>
                                <th scope="col">#</th>
                                <th scope="col">Question</th>
                                <th scope="col">Topic</th>
                                <th scope="col">Action</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($candidates as $candidate)
                                    <tr>
                                        <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <p class="work-q-text">{{ $candidate->content }}</p>
                                            @if ($candidate->choices)
                                                <ul class="work-choices" aria-label="Answer choices">
                                                    @foreach (explode(',', $candidate->choices) as $i => $choice)
                                                        <li><b>{{ $letters[$i] ?? '' }}.</b><span>{{ trim($choice) }}</span></li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <td><span class="work-chip">{{ $topicNames[$candidate->topicId] ?? 'Topic' }}</span></td>
                                        <td>
                                            <form method="POST" action="{{ route('courses.exams.replace', [$course, $exam, $question->questionId]) }}" data-pending>
                                                @csrf
                                                <input type="hidden" name="replacementId" value="{{ $candidate->questionId }}">
                                                <button type="submit" class="work-row-btn work-row-btn-primary" data-pending-label="Replacing…" aria-label="Use this question as the replacement">Use this</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p id="candidateNone" class="work-empty-inline" hidden>No questions match your search.</p>
                @endif
            </section>

