<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Replace question · Exam #{{ $exam->examId }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.exams.index', $course) }}">{{ $course->courseName }} exams</a><span aria-hidden="true">/</span><a href="{{ route('courses.exams.show', [$course, $exam]) }}">Exam #{{ $exam->examId }}</a><span aria-hidden="true">/</span><span aria-current="page">Replace question</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Replace question</p>
                    <h1>Choose a replacement<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Pick another <strong>{{ $question->bloomLevel }}</strong> / <strong>{{ $question->questionType }}</strong> question from the question bank. Only questions that keep the Table of Specifications intact are listed.</p>
                </div>
            </header>

            @php $letters = ['A','B','C','D','E','F','G','H','I','J','K','L']; @endphp
            @if ($errors->any())
                <div class="work-alert work-alert-error work-page-alert" role="alert">{{ $errors->first() }}</div>
            @endif

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

            <div class="work-below-directory"><a href="{{ route('courses.exams.show', [$course, $exam]) }}">← Back to exam</a><span>Exam #{{ $exam->examId }}</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>

    <script>
        (function () {
            var input = document.getElementById('candidateFilter');
            if (!input) return;
            var rows = Array.prototype.slice.call(document.querySelectorAll('#candidateTable tbody tr'));
            var count = document.getElementById('candidateCount');
            var none = document.getElementById('candidateNone');
            input.addEventListener('input', function () {
                var q = input.value.trim().toLowerCase();
                var shown = 0;
                rows.forEach(function (row) {
                    var match = !q || row.textContent.toLowerCase().indexOf(q) !== -1;
                    row.hidden = !match;
                    if (match) shown++;
                });
                count.textContent = shown + (shown === 1 ? ' question' : ' questions');
                none.hidden = shown !== 0;
            });
        })();
    </script>
</body>
</html>
