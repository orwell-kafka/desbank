<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ToS - {{ $course->courseName }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><span aria-current="page">{{ $course->courseName }} ToS</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Table of Specifications</p>
                    <h1>Specifications<span class="work-title-period">.</span></h1>
                    <p class="work-intro">A Table of Specifications plans how many items each topic and Bloom level contributes. Generate an exam from any saved plan for <strong>{{ $course->courseName }}</strong>.</p>
                </div>
                <a href="{{ route('courses.tos.create', $course) }}" class="work-action-primary">Create a ToS <span aria-hidden="true">+</span></a>
            </header>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="work-alert work-alert-error work-page-alert" role="alert">{{ $errors->first() }}</div>
            @endif

            <section class="work-directory" aria-labelledby="work-tos-title">
                <div class="work-directory-heading">
                    <div>
                        <p class="work-eyebrow">SAVED PLANS</p>
                        <h2 id="work-tos-title">ToS configurations</h2>
                    </div>
                    <span class="work-count">{{ $tosList->count() }} {{ $tosList->count() === 1 ? 'plan' : 'plans' }}</span>
                </div>

                @if ($tosList->isEmpty())
                    <div class="work-empty">
                        <span class="work-empty-number" aria-hidden="true">00</span>
                        <h3>No ToS configurations yet.</h3>
                        <p>Create a plan from your topics and Bloom levels. You can then generate an exam from it.</p>
                        <a href="{{ route('courses.tos.create', $course) }}" class="work-action-primary">Create your first ToS <span aria-hidden="true">→</span></a>
                    </div>
                @else
                    <div class="work-table-scroll" role="region" aria-label="ToS table" tabindex="0">
                        <table class="work-table">
                            <thead><tr>
                                <th scope="col">No.</th>
                                <th scope="col">Title</th>
                                <th scope="col">Exam type</th>
                                <th scope="col" class="work-num">Total score</th>
                                <th scope="col">Manage</th>
                            </tr></thead>
                            <tbody>
                                @foreach ($tosList as $tos)
                                    <tr>
                                        <td class="work-table-num">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                                        <td class="work-tos-title">{{ $tos->title }}</td>
                                        <td><span class="work-chip work-chip-gold">{{ $tos->examType }}</span></td>
                                        <td class="work-num">{{ $tos->totalScore }}</td>
                                        <td>
                                            <div class="work-row-actions">
                                                <form method="POST" action="{{ route('courses.tos.generate', [$course, $tos]) }}" data-pending>
                                                    @csrf
                                                    <button type="submit" class="work-row-btn work-row-btn-primary" data-pending-label="Generating…">Generate exam</button>
                                                </form>
                                                <a href="{{ route('courses.tos.show', [$course, $tos]) }}" class="work-row-link work-row-link-main">View <span aria-hidden="true">↗</span></a>
                                                <a href="{{ route('courses.tos.edit', [$course, $tos]) }}" class="work-row-link">Edit</a>
                                                <form method="POST" action="{{ route('courses.tos.destroy', [$course, $tos]) }}" onsubmit="return confirm('Delete this ToS?');">
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

            <div class="work-below-directory"><a href="{{ route('courses.index') }}">← Back to courses</a><span>Topics / ToS / Exams</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
