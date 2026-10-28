<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Topics - {{ $course->courseName }} - DESBank</title>
    @include('partials.head-assets')
</head>
<body>
    <div class="db-shell">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Topics</h1>
            <p class="db-subtitle">{{ $course->courseName }} ({{ $course->courseCode }})</p>

            @if (session('status'))
                <div class="db-alert db-alert-success">{{ session('status') }}</div>
            @endif

            <a href="{{ route('courses.topics.create', $course) }}" class="db-btn db-btn-primary" style="margin-bottom:15px; display:inline-block;">+ Add Topic</a>

            @if ($topics->isEmpty())
                <p style="color:var(--text-muted); font-style:italic;">No topics yet. Add one above.</p>
            @else
                <table class="db-table">
                    <tr><th>Topic Name</th><th>Actions</th></tr>
                    @foreach ($topics as $topic)
                        <tr>
                            <td>{{ $topic->topicName }}</td>
                            <td>
                                <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}" class="db-btn db-btn-secondary">Questions</a>
                                <a href="{{ route('courses.topics.edit', [$course, $topic]) }}" class="db-btn db-btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('courses.topics.destroy', [$course, $topic]) }}" style="display:inline" onsubmit="return confirm('Delete this topic?');">
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