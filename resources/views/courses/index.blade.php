<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Courses · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><span aria-current="page">Courses</span>
            </nav>
            <style>
                .dc-top { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin: 6px 0 22px; flex-wrap: wrap; }
                .dc-top h1 { font-size: 28px; line-height: 1.15; color: #12263a; margin: 0; letter-spacing: -.01em; }
                .dc-top p { margin: 4px 0 0; font-size: 14px; color: #607282; }
                .dc-add { display: inline-flex; align-items: center; gap: 8px; background: #12263a; color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 10px 18px; border-radius: 10px; }
                .dc-add:hover { background: #1d3d5c; }
                .dc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 18px; list-style: none; margin: 0; padding: 0; }
                .dc-card { position: relative; display: flex; flex-direction: column; background: #fff; border: 1px solid #dde4ea; border-left: 6px solid var(--c); border-radius: 12px; padding: 16px 16px 12px 18px; transition: transform .15s ease, box-shadow .15s ease; }
                .dc-card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(18, 38, 58, .1); }
                .dc-card:focus-within { outline: 2px solid var(--c); outline-offset: 2px; }
                .dc-row { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
                .dc-code { display: inline-block; font: 600 12px/1 ui-monospace, 'IBM Plex Mono', monospace; letter-spacing: .04em; color: var(--c); background: color-mix(in srgb, var(--c) 12%, #fff); padding: 6px 9px; border-radius: 6px; }
                .dc-title { display: block; margin: 14px 0 4px; font-size: 19px; line-height: 1.3; font-weight: 700; color: #12263a; text-decoration: none; }
                .dc-title::before { content: ''; position: absolute; inset: 0; z-index: 1; border-radius: 12px; }
                .dc-units { margin: 0; font-size: 13px; color: #607282; min-height: 18px; }
                .dc-links { display: flex; gap: 6px; margin-top: 16px; padding-top: 10px; border-top: 1px dashed #dde4ea; position: relative; z-index: 2; }
                .dc-links a { font-size: 13px; font-weight: 600; color: #2a5375; text-decoration: none; padding: 5px 9px; border-radius: 6px; }
                .dc-links a:hover { background: #eef3f7; color: #12263a; }
                .dc-menu { position: relative; z-index: 3; }
                .dc-menu > summary { list-style: none; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #607282; }
                .dc-menu > summary::-webkit-details-marker { display: none; }
                .dc-menu > summary:hover, .dc-menu[open] > summary { background: #eef3f7; color: #12263a; }
                .dc-menu svg { width: 20px; height: 20px; fill: currentColor; }
                .dc-menu-list { position: absolute; right: 0; top: 36px; min-width: 150px; background: #fff; border: 1px solid #dde4ea; border-radius: 10px; box-shadow: 0 10px 24px rgba(18, 38, 58, .14); padding: 6px; z-index: 5; }
                .dc-menu-list a, .dc-menu-list button { display: block; width: 100%; text-align: left; appearance: none; border: 0; background: transparent; font: 500 14px inherit; font-family: inherit; color: #12263a; padding: 8px 10px; border-radius: 6px; text-decoration: none; cursor: pointer; }
                .dc-menu-list a:hover, .dc-menu-list button:hover { background: #eef3f7; }
                .dc-menu-list form { margin: 0; }
                .dc-menu-list .dc-del { color: #b03a3a; }
                .dc-menu-list .dc-del:hover { background: #fbeded; }
                .dc-empty { text-align: center; padding: 64px 16px; color: #607282; }
                .dc-empty h2 { font-size: 22px; color: #12263a; margin: 0 0 8px; }
                .dc-empty p { margin: 0 0 20px; font-size: 14px; }
                @media (prefers-reduced-motion: reduce) { .dc-card { transition: none; } .dc-card:hover { transform: none; } }
            </style>

            <div class="dc-top">
                <div>
                    <h1>My courses</h1>
                    <p>{{ $courses->count() }} {{ $courses->count() === 1 ? 'course' : 'courses' }}. Open one to manage its question bank and exams.</p>
                </div>
                <a href="{{ route('courses.create') }}" class="dc-add"><span aria-hidden="true">+</span> Add a course</a>
            </div>

            @if (session('status'))
                <div class="work-notice" role="status">{{ session('status') }}</div>
            @endif

            @if ($courses->isEmpty())
                <div class="dc-empty">
                    <h2>No courses yet</h2>
                    <p>Add a course to start building its question bank and exams.</p>
                    <a href="{{ route('courses.create') }}" class="dc-add"><span aria-hidden="true">+</span> Add a course</a>
                </div>
            @else
                <ul class="dc-grid">
                    @foreach ($courses as $course)
                        <li class="dc-card" style="--c: {{ \App\Support\CourseTheme::color($course) }};">
                            <div class="dc-row">
                                <span class="dc-code">{{ $course->courseCode }}</span>
                                <details class="dc-menu">
                                    <summary aria-label="More options for {{ $course->courseName }}" title="More options"><svg viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg></summary>
                                    <div class="dc-menu-list">
                                        <a href="{{ route('courses.show', $course) }}">Open</a>
                                        <a href="{{ route('courses.edit', $course) }}">Edit</a>
                                        <form method="POST" action="{{ route('courses.destroy', $course) }}" data-confirm="Delete this course?" data-confirm-item="{{ $course->courseName }}" data-confirm-label="Delete course">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dc-del">Delete</button>
                                        </form>
                                    </div>
                                </details>
                            </div>
                            <a href="{{ route('courses.show', $course) }}" class="dc-title">{{ $course->courseName }}</a>
                            <p class="dc-units">{{ $course->creditUnits !== null ? \App\Support\TosHeading::units($course->creditUnits) : '' }}</p>
                            <div class="dc-links">
                                <a href="{{ route('courses.topics.index', $course) }}">Question bank</a>
                                <a href="{{ route('courses.exams.index', $course) }}">Exams</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <script>
                    document.addEventListener('click', function (e) {
                        document.querySelectorAll('.dc-menu[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
                    });
                </script>
            @endif
            <div class="work-below-directory"><a href="{{ route('dashboard.instructor') }}">← Back to instructor workspace</a><span>Courses / Topics / Questions / Exams</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
