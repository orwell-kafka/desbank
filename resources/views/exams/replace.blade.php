<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Replace question · {{ $tosTitle }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Replace question</p>
                    <h1>Choose a replacement<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Pick another <strong>{{ $question->bloomLevel }}</strong> / <strong>{{ $question->questionType }}</strong> question from the question bank. Only questions that keep the Table of Specifications intact are listed.</p>
                </div>
            </header>

            @if ($errors->any())
                <div class="work-alert work-alert-error work-page-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            @include('exams.partials.replace-picker')

            <div class="work-below-directory"><a data-back href="{{ route('courses.exams.show', [$course, $exam]) }}">← Back to exam</a><span>{{ $tosTitle }}</span></div>
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
