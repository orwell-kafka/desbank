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
                    <p class="work-intro">Replace any question with another from the same topic and type, then format and print the paper.</p>
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
                    <span class="work-count">{{ count($questions) }} {{ count($questions) === 1 ? 'item' : 'items' }}</span>
                </div>
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
                                <tr>
                                    <td class="work-table-num">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</td>
                                    <td><p class="work-q-text">{{ $question->content }}</p></td>
                                    <td>
                                        <span class="work-chip">{{ $question->bloomLevel }}</span>
                                        <span class="work-chip work-chip-gold">{{ $question->questionType }}</span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('courses.exams.replace', [$course, $exam, $question->questionId]) }}" data-pending>
                                            @csrf
                                            <button type="submit" class="work-row-btn" data-pending-label="Replacing…" aria-label="Auto-replace question {{ $i + 1 }}">Auto-replace</button>
                                        </form>
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
</body>
</html>
