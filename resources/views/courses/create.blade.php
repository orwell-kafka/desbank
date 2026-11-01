<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Course · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><span aria-current="page">New course</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Course directory</p>
                    <h1>Add a course<span class="work-title-period">.</span></h1>
                    <p class="work-intro">A course is the subject you teach. Topics, questions, Tables of Specifications, and exams all hang from it.</p>
                </div>
            </header>

            <div class="work-form-layout">
                <form method="POST" action="{{ route('courses.store') }}" class="work-form" data-pending>
                    @csrf
                    @include('partials.work-errors')

                    <section class="work-section work-section-flush" aria-label="Course details">
                        <div class="work-section-body">
                            <div class="work-field">
                                <label class="work-label" for="courseName">Course name</label>
                                <input type="text" id="courseName" name="courseName" class="work-input {{ $errors->has('courseName') ? 'is-invalid' : '' }}" value="{{ old('courseName') }}" required autofocus @if ($errors->has('courseName')) aria-invalid="true" @endif>
                            </div>
                            <div class="work-field">
                                <label class="work-label" for="courseCode">Course code</label>
                                <input type="text" id="courseCode" name="courseCode" class="work-input {{ $errors->has('courseCode') ? 'is-invalid' : '' }}" value="{{ old('courseCode') }}" required @if ($errors->has('courseCode')) aria-invalid="true" @endif>
                            </div>
                        </div>
                    </section>

                    <div class="work-form-actions">
                        <button type="submit" class="work-action-primary" data-pending-label="Saving…">Save course <span aria-hidden="true">→</span></button>
                        <a href="{{ route('courses.index') }}" class="work-form-cancel">Cancel</a>
                    </div>
                </form>

                <aside class="work-aside" aria-label="What a course contains">
                    <h2>What a course holds</h2>
                    <ol>
                        <li><span class="work-step-num">01</span><div><strong>Topics</strong><p>Group your questions by topic.</p></div></li>
                        <li><span class="work-step-num">02</span><div><strong>Question bank</strong><p>Add questions by type and Bloom level.</p></div></li>
                        <li><span class="work-step-num">03</span><div><strong>Table of Specifications</strong><p>Plan how many items each topic and level gets.</p></div></li>
                        <li><span class="work-step-num">04</span><div><strong>Exams</strong><p>Generate, format, and print with an answer key.</p></div></li>
                    </ol>
                </aside>
            </div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
