<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Topic · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.topics.index', $course) }}">{{ $course->courseName }}</a><span aria-hidden="true">/</span><span aria-current="page">Edit topic</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Topics</p>
                    <h1>Edit topic<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Renaming a topic does not move or change its questions.</p>
                </div>
            </header>

            <div class="work-form-layout">
                <form method="POST" action="{{ route('courses.topics.update', [$course, $topic]) }}" class="work-form" data-pending>
                    @csrf
                    @method('PUT')
                    @include('partials.work-errors')

                    <section class="work-section work-section-flush" aria-label="Topic details">
                        <div class="work-section-body">
                            <div class="work-field">
                                <label class="work-label" for="topicName">Topic name</label>
                                <input type="text" id="topicName" name="topicName" class="work-input {{ $errors->has('topicName') ? 'is-invalid' : '' }}" value="{{ old('topicName', $topic->topicName) }}" required autofocus @if ($errors->has('topicName')) aria-invalid="true" @endif>
                            </div>
                        </div>
                    </section>

                    <div class="work-form-actions">
                        <button type="submit" class="work-action-primary" data-pending-label="Updating…">Update topic <span aria-hidden="true">→</span></button>
                        <a href="{{ route('courses.topics.index', $course) }}" class="work-form-cancel">Cancel</a>
                    </div>
                </form>

                <aside class="work-aside" aria-label="Course context">
                    <div class="work-aside-context">
                        <h2>In course</h2>
                        <span class="work-course-code">{{ $course->courseCode }}</span>
                        <strong>{{ $course->courseName }}</strong>
                    </div>
                    <h2>Next</h2>
                    <ol>
                        <li><span class="work-step-num">01</span><div><strong>Add questions</strong><p>Each topic has its own question bank, sorted by type and Bloom level.</p></div></li>
                        <li><span class="work-step-num">02</span><div><strong>Plan the exam</strong><p>Topics appear in the Table of Specifications with their contact hours.</p></div></li>
                    </ol>
                </aside>
            </div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
