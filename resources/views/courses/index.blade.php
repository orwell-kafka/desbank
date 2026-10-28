<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Courses - DESBank</title>
    @include('partials.head-assets')
</head>
<body>
    <div class="db-shell">
        @include('partials.navbar')
        <div class="db-card">
            <h1>My Courses</h1>

            @if (session('status'))
                <div class="db-alert db-alert-success">{{ session('status') }}</div>
            @endif

            <a href="{{ route('courses.create') }}" class="db-btn db-btn-primary" style="margin-bottom:15px; display:inline-block;">+ Add Course</a>

            @if ($courses->isEmpty())
                <p style="color:var(--text-muted); font-style:italic;">No courses yet. Add one above.</p>
            @else
                <table class="db-table">
                    <tr><th>Course Code</th><th>Course Name</th><th>Actions</th></tr>
                    @foreach ($courses as $course)
                        <tr>
                            <td style="font-family:var(--font-mono);">{{ $course->courseCode }}</td>
                            <td>{{ $course->courseName }}</td>
                            <td>
                                <a href="{{ route('courses.topics.index', $course) }}" class="db-btn db-btn-secondary">Topics</a>
                                <a href="{{ route('courses.tos.index', $course) }}" class="db-btn db-btn-secondary">ToS</a>
                                <a href="{{ route('courses.exams.index', $course) }}" class="db-btn db-btn-secondary">Exams</a>
                                <a href="{{ route('courses.edit', $course) }}" class="db-btn db-btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('courses.destroy', $course) }}" style="display:inline" onsubmit="return confirm('Delete this course?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="db-btn db-btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @endif

            <a href="{{ route('dashboard.instructor') }}" class="db-link db-back-link">&larr; Back to Dashboard</a>
        </div>
    </div>
</body>
</html>