<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Course · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><span aria-current="page">Edit course</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Course directory</p>
                    <h1>Edit course<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Changing the name or code updates it everywhere this course appears. Its topics, questions, and exams are not affected.</p>
                </div>
            </header>

            <div class="work-form-layout">
                <form method="POST" action="{{ route('courses.update', $course) }}" class="work-form" data-pending>
                    @csrf
                    @method('PUT')
                    @include('partials.work-errors')

                    <section class="work-section work-section-flush" aria-label="Course details">
                        <div class="work-section-body">
                            <div class="work-field">
                                <label class="work-label" for="courseName">Course name</label>
                                <input type="text" id="courseName" name="courseName" class="work-input {{ $errors->has('courseName') ? 'is-invalid' : '' }}" value="{{ old('courseName', $course->courseName) }}" required autofocus @if ($errors->has('courseName')) aria-invalid="true" @endif>
                            </div>
                            <div class="work-field">
                                <label class="work-label" for="courseCode">Course code</label>
                                <input type="text" id="courseCode" name="courseCode" class="work-input {{ $errors->has('courseCode') ? 'is-invalid' : '' }}" value="{{ old('courseCode', $course->courseCode) }}" required @if ($errors->has('courseCode')) aria-invalid="true" @endif>
                            </div>
                        </div>
                    </section>

                    <div class="work-form-actions">
                        <button type="submit" class="work-action-primary" data-pending-label="Updating…">Update course <span aria-hidden="true">→</span></button>
                        <a href="{{ route('courses.index') }}" class="work-form-cancel">Cancel</a>
                    </div>
                </form>

                <aside class="work-aside" aria-label="Course shortcuts">
                    <div class="work-aside-context">
                        <h2>Editing</h2>
                        <span class="work-course-code">{{ $course->courseCode }}</span>
                        <strong>{{ $course->courseName }}</strong>
                    </div>
                    <h2>Open this course</h2>
                    <ol>
                        <li><span class="work-step-num">→</span><div><strong><a class="work-row-link work-row-link-main" href="{{ route('courses.topics.index', $course) }}">Topics</a></strong></div></li>
                        <li><span class="work-step-num">→</span><div><strong><a class="work-row-link work-row-link-main" href="{{ route('courses.tos.index', $course) }}">Table of Specifications</a></strong></div></li>
                        <li><span class="work-step-num">→</span><div><strong><a class="work-row-link work-row-link-main" href="{{ route('courses.exams.index', $course) }}">Exams</a></strong></div></li>
                    </ol>
                </aside>
            </div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
