<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Exams - {{ $course->courseName }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><span aria-current="page">{{ $course->courseName }} exams</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Generated exams</p>
                    <h1>Exams<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Review exams generated from a Table of Specifications, replace individual questions, then format and print.</p>
                </div>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif

            <section class="work-directory" aria-labelledby="work-exams-title">
                <div class="work-directory-heading">
                    <div>
                        <p class="work-eyebrow">GENERATED</p>
                        <h2 id="work-exams-title">Exam list</h2>
                    </div>
                    <span class="work-count">{{ $exams->count() }} {{ $exams->count() === 1 ? 'exam' : 'exams' }}</span>
                </div>

                @if ($exams->isEmpty())
                    <div class="work-empty">
                        <span class="work-empty-number" aria-hidden="true">00</span>
                        <h3>No exams generated yet.</h3>
                        <p>Open a Table of Specifications and choose "Generate exam."</p>
                        <a href="{{ route('courses.tos.index', $course) }}" class="work-action-primary">Go to ToS <span aria-hidden="true">→</span></a>
                    </div>
                @else
                    <div class="work-table-scroll" role="region" aria-label="Exams table" tabindex="0">
                        <table class="work-table">
                            <thead><tr>
                                <th scope="col">No.</th>
                                <th scope="col">ToS</th>
                                <th scope="col" class="work-num">Total items</th>
                                <th scope="col" class="work-num">Total score</th>
                                <th scope="col">Created</th>
                                <th scope="col">Manage</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($exams as $exam)
                                    <tr>
                                        <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td class="work-tos-title">{{ $exam->tosTitle }}</td>
                                        <td class="work-num">{{ $exam->totalItems }}</td>
                                        <td class="work-num">{{ $exam->totalScore }}</td>
                                        <td class="work-email">{{ $exam->created }}</td>
                                        <td>
                                            <div class="work-row-actions">
                                                <a href="{{ route('courses.exams.show', [$course, $exam]) }}" class="work-row-link work-row-link-main">View <span aria-hidden="true">↗</span></a>
                                                <a href="{{ route('courses.exams.answerkey', [$course, $exam]) }}" class="work-row-link">Answer key</a>
                                                <form method="POST" action="{{ route('courses.exams.destroy', [$course, $exam]) }}" onsubmit="return confirm('Delete this exam?');">
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

            <div class="work-below-directory"><a href="{{ route('courses.index') }}">← Back to courses</a><span>ToS / Exams</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
