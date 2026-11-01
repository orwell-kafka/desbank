<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Topics - {{ $course->courseName }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><span aria-current="page">{{ $course->courseName }}</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span></p>
                    <h1>Topics<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Questions are organized by topic. Open a topic to add or review its questions, or add a new one for <strong>{{ $course->courseName }}</strong>.</p>
                </div>
                <a href="{{ route('courses.topics.create', $course) }}" class="work-action-primary">Add a topic <span aria-hidden="true">+</span></a>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif

            <section class="work-directory" aria-labelledby="work-topics-title">
                <div class="work-directory-heading">
                    <div>
                        <p class="work-eyebrow">COURSE CONTENT</p>
                        <h2 id="work-topics-title">Topic list</h2>
                    </div>
                    <span class="work-count">{{ $topics->count() }} {{ $topics->count() === 1 ? 'topic' : 'topics' }}</span>
                </div>

                @if ($topics->isEmpty())
                    <div class="work-empty">
                        <span class="work-empty-number" aria-hidden="true">00</span>
                        <h3>No topics yet.</h3>
                        <p>Add the first topic for this course. Each topic holds its own question bank, and a Table of Specifications needs at least one.</p>
                        <a href="{{ route('courses.topics.create', $course) }}" class="work-action-primary">Add the first topic <span aria-hidden="true">→</span></a>
                    </div>
                @else
                    <div class="work-table-scroll" role="region" aria-label="Topics table" tabindex="0">
                        <table class="work-table work-table-narrow">
                            <thead><tr>
                                <th scope="col">No.</th>
                                <th scope="col">Topic name</th>
                                <th scope="col">Manage</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($topics as $topic)
                                    <tr>
                                        <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td class="work-course-name">{{ $topic->topicName }}</td>
                                        <td>
                                            <div class="work-row-actions">
                                                <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}" class="work-row-link work-row-link-main">Questions <span aria-hidden="true">↗</span></a>
                                                <a href="{{ route('courses.topics.edit', [$course, $topic]) }}" class="work-row-link">Edit</a>
                                                <form method="POST" action="{{ route('courses.topics.destroy', [$course, $topic]) }}" onsubmit="return confirm('Delete this topic?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="work-row-link work-row-delete">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <div class="work-below-directory"><a href="{{ route('courses.index') }}">← Back to courses</a><span>Topics / Questions</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
