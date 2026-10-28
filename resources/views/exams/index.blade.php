<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exams - {{ $course->courseName }} - DESBank</title>
    @include('partials.head-assets')
</head>
<body>
    <div class="db-shell">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Generated Exams</h1>
            <p class="db-subtitle">{{ $course->courseName }} ({{ $course->courseCode }})</p>

            @if (session('status'))
                <div class="db-alert db-alert-success">{{ session('status') }}</div>
            @endif

            @if ($exams->isEmpty())
                <p style="color:var(--text-muted); font-style:italic;">No exams generated yet. Go to a ToS and click "Generate Exam."</p>
            @else
                <table class="db-table">
                    <tr><th>ToS</th><th>Total Items</th><th>Total Score</th><th>Created</th><th>Actions</th></tr>
                    @foreach ($exams as $exam)
                        <tr>
                            <td>{{ $exam->tosTitle }}</td>
                            <td style="font-family:var(--font-mono);">{{ $exam->totalItems }}</td>
                            <td style="font-family:var(--font-mono);">{{ $exam->totalScore }}</td>
                            <td>{{ $exam->created }}</td>
                            <td>
                                <a href="{{ route('courses.exams.show', [$course, $exam]) }}" class="db-btn db-btn-secondary">View</a>
                                <a href="{{ route('courses.exams.answerkey', [$course, $exam]) }}" class="db-btn db-btn-secondary">Answer Key</a>
                                <form method="POST" action="{{ route('courses.exams.destroy', [$course, $exam]) }}" style="display:inline" onsubmit="return confirm('Delete this exam?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="db-btn db-btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif

            <a href="{{ route('courses.index') }}" class="db-link db-back-link">&larr; Back to Courses</a>
        </div>
    </div>
</body>
</html>