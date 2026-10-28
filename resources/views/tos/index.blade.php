<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ToS - {{ $course->courseName }} - DESBank</title>
    @include('partials.head-assets')
</head>
<body>
    <div class="db-shell">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Table of Specifications</h1>
            <p class="db-subtitle">{{ $course->courseName }} ({{ $course->courseCode }})</p>

            @if (session('status'))
                <div class="db-alert db-alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
            @endif

            <a href="{{ route('courses.tos.create', $course) }}" class="db-btn db-btn-primary" style="margin-bottom:15px; display:inline-block;">+ Create ToS</a>

            @if ($tosList->isEmpty())
                <p style="color:var(--text-muted); font-style:italic;">No ToS configurations yet. Create one above.</p>
            @else
                <table class="db-table">
                    <tr><th>Title</th><th>Exam Type</th><th>Total Score</th><th>Actions</th></tr>
                    @foreach ($tosList as $tos)
                        <tr>
                            <td>{{ $tos->title }}</td>
                            <td>{{ $tos->examType }}</td>
                            <td style="font-family:var(--font-mono);">{{ $tos->totalScore }}</td>
                            <td>
                                <a href="{{ route('courses.tos.show', [$course, $tos]) }}" class="db-btn db-btn-secondary">View</a>
                                <a href="{{ route('courses.tos.edit', [$course, $tos]) }}" class="db-btn db-btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('courses.tos.generate', [$course, $tos]) }}" style="display:inline">
                                    @csrf
                                    <button type="submit" class="db-btn db-btn-primary">Generate Exam</button>
                                </form>
                                <form method="POST" action="{{ route('courses.tos.destroy', [$course, $tos]) }}" style="display:inline" onsubmit="return confirm('Delete this ToS?');">
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