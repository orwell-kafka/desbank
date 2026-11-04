<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tosTitle }} exam · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.exams.index', $course) }}">{{ $course->courseName }} exams</a><span aria-hidden="true">/</span><span aria-current="page">{{ $tosTitle }}</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Generated exam</p>
                    <h1>{{ $tosTitle }}<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Replace any question by choosing another from the question bank, or let the system pick one, then format and print the paper.</p>
                </div>
                <div class="work-heading-actions">
                    <a href="{{ route('courses.exams.format', [$course, $exam]) }}" class="work-action-primary">Format &amp; print <span aria-hidden="true">→</span></a>
                    <a href="{{ route('courses.exams.answerkey', [$course, $exam]) }}" class="work-action-outline">Answer key <span aria-hidden="true">↗</span></a>
                </div>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="work-alert work-alert-error work-page-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <dl class="work-meta">
                <div><dt>Total items</dt><dd>{{ $exam->totalItems }}</dd></div>
                <div><dt>Total score</dt><dd>{{ $exam->totalScore }}</dd></div>
                <div><dt>Created</dt><dd>{{ $exam->created }}</dd></div>
            </dl>

            <section class="work-directory" aria-labelledby="work-bloom">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">REALIZED</p><h2 id="work-bloom">Bloom's distribution</h2></div>
                </div>
                <div class="work-table-scroll" role="region" aria-label="Bloom distribution" tabindex="0">
                    <table class="work-table work-table-plain work-table-narrow">
                        <thead><tr>
                            <th scope="col">Bloom level</th>
                            <th scope="col" class="work-num">Items</th>
                            <th scope="col" class="work-num">Score</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($bloomConfig as $row)
                                <tr>
                                    <td><span class="work-chip">{{ $row->bloomLevel }}</span></td>
                                    <td class="work-num">{{ $row->numberItems }}</td>
                                    <td class="work-num">{{ $row->numScore }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="work-directory" style="margin-top:0;" aria-labelledby="work-topics">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">REALIZED</p><h2 id="work-topics">Points by topic</h2></div>
                </div>
                <div class="work-table-scroll" role="region" aria-label="Points by topic" tabindex="0">
                    <table class="work-table work-table-plain work-table-narrow">
                        <thead><tr>
                            <th scope="col">Topic</th>
                            <th scope="col" class="work-num">Items</th>
                            <th scope="col" class="work-num">Points</th>
                            <th scope="col" class="work-num">ToS assigned</th>
                        </tr></thead>
                        <tbody>
                            @php $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.'); @endphp
                            @foreach ($topicSummary as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="work-num">{{ $row['items'] }}</td>
                                    <td class="work-num">{{ $fmt($row['points']) }}</td>
                                    <td class="work-num">{{ $row['target'] === null ? '—' : $fmt($row['target']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td class="work-num">{{ collect($topicSummary)->sum('items') }}</td>
                                <td class="work-num">{{ $fmt(collect($topicSummary)->sum('points')) }}</td>
                                <td class="work-num">{{ $fmt(collect($topicSummary)->sum(fn ($r) => $r['target'] ?? 0)) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <section class="work-directory" style="margin-top:0;" aria-labelledby="work-questions">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">EXAM CONTENT</p><h2 id="work-questions">Questions</h2></div>
                    <span class="work-count" id="examItemCount" data-total="{{ count($questions) }}">{{ count($questions) }} {{ count($questions) === 1 ? 'item' : 'items' }}</span>
                </div>

                @php
                    $typeCounts = collect($questions)->countBy('questionType');
                    $topicCounts = collect($questions)->countBy(fn ($q) => $q->topicName ?? '(no topic)');
                @endphp
                @if ($typeCounts->count() > 1)
                    <div class="work-tabs" id="typeFilter" role="group" aria-label="Show questions by type" data-exam="{{ $exam->examId }}">
                        <button type="button" class="work-tab" data-type="" aria-pressed="true">All <small>{{ count($questions) }}</small></button>
                        @foreach ($typeCounts as $type => $count)
                            <button type="button" class="work-tab" data-type="{{ $type }}" aria-pressed="false">{{ $type }} <small>{{ $count }}</small></button>
                        @endforeach
                    </div>
                @endif
                @if ($topicCounts->count() > 1)
                    <div style="margin:12px 29px 0;max-width:420px;">
                        <label class="work-label" for="topicFilter">Filter by topic</label>
                        <select id="topicFilter" class="work-select" data-exam="{{ $exam->examId }}">
                            <option value="">All topics ({{ count($questions) }})</option>
                            @foreach ($topicCounts as $topicName => $count)
                                <option value="{{ $topicName }}">{{ $topicName }} ({{ $count }})</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if ($typeCounts->count() > 1 || $topicCounts->count() > 1)
                    <p class="work-hint" id="typeFilterNote" role="status" aria-live="polite" style="margin:10px 29px 0;" hidden></p>
                @endif

                <div class="work-table-scroll" role="region" aria-label="Exam questions" tabindex="0">
                    <table class="work-table">
                        <thead><tr>
                            <th scope="col">#</th>
                            <th scope="col">Question</th>
                            <th scope="col">Topic</th>
                            <th scope="col">Tags</th>
                            <th scope="col">Action</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($questions as $i => $question)
                                <tr data-type="{{ $question->questionType }}" data-topic="{{ $question->topicName ?? '(no topic)' }}">
                                    <td class="work-table-num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                                    <td><p class="work-q-text">{{ $question->content }}</p></td>
                                    <td>{{ $question->topicName ?? '(no topic)' }}</td>
                                    <td>
                                        <span class="work-chip">{{ $question->bloomLevel }}</span>
                                        <span class="work-chip work-chip-gold">{{ $question->questionType }}</span>
                                    </td>
                                    <td>
                                        <div class="work-row-actions">
                                            <a href="{{ route('courses.exams.replace.options', [$course, $exam, $question->questionId]) }}" class="work-row-btn work-row-btn-primary" style="text-decoration:none;" aria-label="Choose a replacement for question {{ $i + 1 }}">Choose…</a>
                                            <form method="POST" action="{{ route('courses.exams.replace', [$course, $exam, $question->questionId]) }}" data-pending>
                                                @csrf
                                                <button type="submit" class="work-row-btn" data-pending-label="Replacing…" aria-label="Auto-replace question {{ $i + 1 }}">Auto-replace</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="work-below-directory"><a href="{{ route('courses.exams.index', $course) }}">← Back to exams</a><span>{{ $tosTitle }}</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>

    <script>
        // Temporary view filters (question type + topic) for the exam's questions. They only hide
        // rows: the order and the original item numbers never change, and "All" brings everything back.
        (function () {
            var typeBar = document.getElementById('typeFilter');
            var topicSel = document.getElementById('topicFilter');
            if (!typeBar && !topicSel) return;
            var rows = Array.prototype.slice.call(document.querySelectorAll('tbody tr[data-type]'));
            var count = document.getElementById('examItemCount');
            var note = document.getElementById('typeFilterNote');
            var total = parseInt(count.getAttribute('data-total'), 10) || rows.length;
            var examId = (typeBar || topicSel).getAttribute('data-exam');
            var typeKey = 'desbank.examTypeFilter.' + examId;
            var topicKey = 'desbank.examTopicFilter.' + examId;
            var state = { type: '', topic: '' };

            function apply() {
                var shown = 0;
                rows.forEach(function (row) {
                    var match = (!state.type || row.getAttribute('data-type') === state.type) &&
                                (!state.topic || row.getAttribute('data-topic') === state.topic);
                    row.hidden = !match;
                    if (match) shown++;
                });
                if (typeBar) {
                    Array.prototype.forEach.call(typeBar.querySelectorAll('[data-type]'), function (btn) {
                        var on = btn.getAttribute('data-type') === state.type;
                        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                        if (on) btn.setAttribute('aria-current', 'page'); else btn.removeAttribute('aria-current');
                    });
                }
                if (topicSel) topicSel.value = state.topic;
                if (state.type || state.topic) {
                    var parts = [];
                    if (state.type) parts.push(state.type);
                    if (state.topic) parts.push('topic "' + state.topic + '"');
                    count.textContent = shown + ' of ' + total + ' items';
                    if (note) {
                        note.textContent = 'Showing only ' + parts.join(' in ') + '. Item numbers match the full exam.';
                        note.hidden = false;
                    }
                } else {
                    count.textContent = total + (total === 1 ? ' item' : ' items');
                    if (note) note.hidden = true;
                }
            }

            function save(key, value) {
                try { value ? sessionStorage.setItem(key, value) : sessionStorage.removeItem(key); } catch (err) {}
            }

            if (typeBar) {
                typeBar.addEventListener('click', function (e) {
                    var btn = e.target.closest('[data-type]');
                    if (!btn) return;
                    state.type = btn.getAttribute('data-type');
                    save(typeKey, state.type);
                    apply();
                });
            }
            if (topicSel) {
                topicSel.addEventListener('change', function () {
                    state.topic = topicSel.value;
                    save(topicKey, state.topic);
                    apply();
                });
            }

            // Keep the chosen filters after replacing a question (the page reloads), for this tab only.
            function restore(key) { try { return sessionStorage.getItem(key) || ''; } catch (err) { return ''; } }
            var savedType = restore(typeKey), savedTopic = restore(topicKey);
            if (typeBar && savedType) {
                var esc = window.CSS && CSS.escape ? CSS.escape(savedType) : savedType;
                if (typeBar.querySelector('[data-type="' + esc + '"]')) state.type = savedType;
            }
            if (topicSel && savedTopic) {
                var found = Array.prototype.some.call(topicSel.options, function (o) { return o.value === savedTopic; });
                if (found) state.topic = savedTopic;
            }
            apply();
        })();
    </script>
</body>
</html>
