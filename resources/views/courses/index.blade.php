<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Courses · DESBank</title>
    @include('partials.head-assets')
    <link rel="stylesheet" href="{{ asset('css/workspace.css') }}">
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><span aria-current="page">Courses</span>
            </nav>
            <header class="work-heading work-heading-courses">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Course directory</p>
                    <h1>My courses<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Organize teaching material and open the question bank, exam plans, or generated assessments for each subject.</p>
                </div>
                <a href="{{ route('courses.create') }}" class="work-action-primary work-create">Add a course <span aria-hidden="true">+</span></a>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif

            <section class="work-directory" aria-labelledby="work-directory-title">
                <div class="work-directory-heading">
                    <div>
                        <p class="work-eyebrow">YOUR SUBJECTS</p>
                        <h2 id="work-directory-title">Course directory</h2>
                    </div>
                    <span class="work-count">{{ $courses->count() }} {{ $courses->count() === 1 ? 'course' : 'courses' }}</span>
                </div>

                @if ($courses->isEmpty())
                    <div class="work-empty">
                        <span class="work-empty-number" aria-hidden="true">00</span>
                        <h3>No courses created yet.</h3>
                        <p>Start with a course. You'll be able to add topics and questions, plan a Table of Specifications, and build exams from there.</p>
                        <a href="{{ route('courses.create') }}" class="work-action-primary">Create your first course <span aria-hidden="true">→</span></a>
                    </div>
                @else
                    <div class="work-table-scroll" role="region" aria-label="Courses table" tabindex="0">
                        <table class="work-table">
                            <thead><tr>
                                <th scope="col">No.</th>
                                <th scope="col">Course code</th>
                                <th scope="col">Course name</th>
                                <th scope="col">Manage</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($courses as $course)
                                    <tr>
                                        <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td><span class="work-course-code">{{ $course->courseCode }}</span></td>
                                        <td class="work-course-name">{{ $course->courseName }}</td>
                                        <td>
                                            <div class="work-row-actions">
                                                <a href="{{ route('courses.topics.index', $course) }}" class="work-row-link work-row-link-main">Topics <span aria-hidden="true">↗</span></a>
                                                <a href="{{ route('courses.tos.index', $course) }}" class="work-row-link">ToS</a>
                                                <a href="{{ route('courses.exams.index', $course) }}" class="work-row-link">Exams</a>
                                                <a href="{{ route('courses.edit', $course) }}" class="work-row-link">Edit</a>
                                                <form method="POST" action="{{ route('courses.destroy', $course) }}" onsubmit="return confirm('Delete this course?');">
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

            <div class="work-below-directory"><a href="{{ route('dashboard.instructor') }}">← Back to instructor workspace</a><span>Courses / Topics / Questions / Exams</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
