<div class="db-navbar">
    <a href="{{ route('dashboard.instructor') }}" class="db-nav-primary">DESBank</a>
    <span class="db-nav-divider">/</span>
    <a href="{{ route('courses.index') }}">My Courses</a>

    @isset($course)
        <span class="db-nav-divider">/</span>
        <span class="db-nav-context">{{ $course->courseName }}</span>
        <a href="{{ route('courses.topics.index', $course) }}">Topics</a>
        <a href="{{ route('courses.tos.index', $course) }}">ToS</a>
        <a href="{{ route('courses.exams.index', $course) }}">Exams</a>
    @endisset

    @isset($topic)
        <span class="db-nav-divider">/</span>
        <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}">{{ $topic->topicName }} Questions</a>
    @endisset
</div>