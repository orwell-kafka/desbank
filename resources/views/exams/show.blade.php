<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exam #{{ $exam->examId }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.exams.index', $course) }}">{{ $course->courseName }} exams</a><span aria-hidden="true">/</span><span aria-current="page">Exam #{{ $exam->examId }}</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Generated exam</p>
                    <h1>Exam #{{ $exam->examId }}<span class="work-title-period">.</span></h1>
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

            <section class="work-directory" style="margin-top:0;" aria-labelledby="work-questions">
                <div class="work-directory-heading">
                    <div><p class="work-eyebrow">EXAM CONTENT</p><h2 id="work-questions">Questions</h2></div>
                    <span class="work-count" id="examItemCount" data-total="{{ count($questions) }}">{{ count($questions) }} {{ count($questions) === 1 ? 'item' : 'items' }}</span>
                </div>

                @php $typeCounts = collect($questions)->countBy('questionType'); @endphp
                @if ($typeCounts->count() > 1)
                    <div class="work-tabs" id="typeFilter" role="group" aria-label="Show questions by type" data-exam="{{ $exam->examId }}">
                        <button type="button" class="work-tab" data-type="" aria-pressed="true">All <small>{{ count($questions) }}</small></button>
                        @foreach ($typeCounts as $type => $count)
                            <button type="button" class="work-tab" data-type="{{ $type }}" aria-pressed="false">{{ $type }} <small>{{ $count }}</small></button>
                        @endforeach
                    </div>
                    <p class="work-hint" id="typeFilterNote" role="status" aria-live="polite" style="margin:10px 29px 0;" hidden></p>
                @endif

                <div class="work-table-scroll" role="region" aria-label="Exam questions" tabindex="0">
                    <table class="work-table">
                        <thead><tr>
                            <th scope="col">#</th>
                            <th scope="col">Question</th>
                            <th scope="col">Tags</th>
                            <th scope="col">Action</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($questions as $i => $question)
                                <tr data-type="{{ $question->questionType }}">
                                    <td class="work-table-num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                                    <td><p class="work-q-text">{{ $question->content }}</p></td>
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

            <div class="work-below-directory"><a href="{{ route('courses.exams.index', $course) }}">← Back to exams</a><span>Exam #{{ $exam->examId }}</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>

    <script>
        // Temporary view filter for the exam's questions. It only hides rows: the order and the
        // original item numbers never change, and "All" always brings everything back.
        (function () {
            var bar = document.getElementById('typeFilter');
            if (!bar) return;
            var rows = Array.prototype.slice.call(document.querySelectorAll('tbody tr[data-type]'));
            var count = document.getElementById('examItemCount');
            var note = document.getElementById('typeFilterNote');
            var total = parseInt(count.getAttribute('data-total'), 10) || rows.length;
            var storeKey = 'desbank.examTypeFilter.' + bar.getAttribute('data-exam');

            function apply(type) {
                var shown = 0;
                rows.forEach(function (row) {
                    var match = !type || row.getAttribute('data-type') === type;
                    row.hidden = !match;
                    if (match) shown++;
                });
                Array.prototype.forEach.call(bar.querySelectorAll('[data-type]'), function (btn) {
                    var on = btn.getAttribute('data-type') === type;
                    btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                    if (on) btn.setAttribute('aria-current', 'page'); else btn.removeAttribute('aria-current');
                });
                if (type) {
                    count.textContent = shown + ' of ' + total + ' items';
                    note.textContent = 'Showing only ' + type + '. Item numbers match the full exam.';
                    note.hidden = false;
                } else {
                    count.textContent = total + (total === 1 ? ' item' : ' items');
                    note.hidden = true;
                }
            }

            bar.addEventListener('click', function (e) {
                var btn = e.target.closest('[data-type]');
                if (!btn) return;
                var type = btn.getAttribute('data-type');
                apply(type);
                try { type ? sessionStorage.setItem(storeKey, type) : sessionStorage.removeItem(storeKey); } catch (err) {}
            });

            // Keep the chosen type after replacing a question (the page reloads), for this tab only.
            var saved = '';
            try { saved = sessionStorage.getItem(storeKey) || ''; } catch (err) {}
            var valid = saved && bar.querySelector('[data-type="' + (window.CSS && CSS.escape ? CSS.escape(saved) : saved) + '"]');
            apply(valid ? saved : '');
        })();
    </script>
</body>
</html>
