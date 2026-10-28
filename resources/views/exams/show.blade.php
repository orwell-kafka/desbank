<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generated Exam - DESBank</title>
    @include('partials.head-assets')
    <style>
        h3 { font-family: var(--font-display); font-size: 15px; color: var(--navy); margin-top: 28px; border-bottom: 2px solid var(--border); padding-bottom: 6px; }
        .meta { background: var(--canvas); border: 1px solid var(--border); padding: 12px 16px; border-radius: var(--radius-sm); font-size: 14px; margin-top: 15px; }
        .meta span { display: inline-block; margin-right: 20px; }
        .replace-btn { background: var(--amber); color: var(--navy-dark); border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer; font-weight: 600; }
        .replace-btn:hover { background: var(--amber-dark); }
    </style>
</head>
<body>
    <div class="db-shell" style="max-width:900px;">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Generated Exam #{{ $exam->examId }}</h1>

            @if (session('status'))
                <div class="db-alert db-alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
            @endif

            <div class="meta">
                <span><strong>Total Items:</strong> {{ $exam->totalItems }}</span>
                <span><strong>Total Score:</strong> {{ $exam->totalScore }}</span>
                <span><strong>Created:</strong> {{ $exam->created }}</span>
            </div>

            <h3>Bloom's Distribution (Realized)</h3>
            <table class="db-table">
                <tr><th>Bloom Level</th><th>Items</th><th>Score</th></tr>
                @foreach ($bloomConfig as $row)
                    <tr>
                        <td><span class="db-chip">{{ $row->bloomLevel }}</span></td>
                        <td>{{ $row->numberItems }}</td>
                        <td>{{ $row->numScore }}</td>
                    </tr>
                @endforeach
            </table>

            <h3>Questions</h3>
            <table class="db-table">
                <tr><th>#</th><th>Question</th><th>Tags</th><th>Action</th></tr>
                @foreach ($questions as $i => $question)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $question->content }}</td>
                        <td>
                            <span class="db-chip">{{ $question->bloomLevel }}</span>
                            <span class="db-chip db-chip-amber">{{ $question->questionType }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('courses.exams.replace', [$course, $exam, $question->questionId]) }}">
                                @csrf
                                <button type="submit" class="replace-btn">Auto-Replace</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>

            <div class="db-link" style="margin-top:20px;">
                <a href="{{ route('courses.exams.format', [$course, $exam]) }}" class="db-btn db-btn-primary">Format &amp; Print This Exam</a>
            </div>
            <div class="db-link" style="margin-top:10px;">
                <a href="{{ route('courses.exams.answerkey', [$course, $exam]) }}" class="db-btn db-btn-secondary">View / Print Answer Key</a>
            </div>

            <a href="{{ route('courses.exams.index', $course) }}" class="db-link db-back-link">&larr; Back to Exams</a>
        </div>
    </div>
</body>
</html>