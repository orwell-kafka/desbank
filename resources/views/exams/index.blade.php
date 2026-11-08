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
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.show', $course) }}">{{ $course->courseName }}</a><span aria-hidden="true">/</span><span aria-current="page">exams</span>
            </nav>
            @php $color = \App\Support\CourseTheme::color($course); @endphp
            <style>
                .ex { --c: {{ $color }}; }
                .ex-back { display: inline-block; margin: 4px 0 16px; font-size: 14px; font-weight: 600; color: #2a5375; text-decoration: none; }
                .ex-back:hover { color: #12263a; text-decoration: underline; }
                .ex-head { background: #fff; border: 1px solid #dde4ea; border-top: 6px solid var(--c); border-radius: 14px; padding: 20px 26px 22px; display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
                .ex-eyebrow { display: flex; align-items: center; gap: 8px; margin: 0; font: 600 12px/1 ui-monospace, 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; color: var(--c); }
                .ex-eyebrow i { font-style: normal; color: #9aa9b6; }
                .ex-head h1 { font-size: 30px; line-height: 1.2; color: #12263a; margin: 12px 0 6px; letter-spacing: -.01em; }
                .ex-head p { margin: 0; font-size: 14px; color: #607282; max-width: 560px; }
                .ex-actions { display: flex; gap: 10px; flex-wrap: wrap; }
                .ex-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-weight: 600; font-size: 14px; padding: 10px 18px; border-radius: 999px; border: 1px solid var(--c); color: var(--c); background: #fff; }
                .ex-btn:hover { background: color-mix(in srgb, var(--c) 8%, #fff); }
                .ex-btn-main { background: var(--c); color: #fff; }
                .ex-btn-main:hover { background: var(--c); filter: brightness(1.12); }
                .ex-bar { display: flex; align-items: center; justify-content: space-between; margin: 24px 0 10px; font-size: 13px; color: #607282; }
                .ex-bar strong { letter-spacing: .08em; text-transform: uppercase; color: #46607a; }
                .ex-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
                .ex-row { position: relative; display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #dde4ea; border-radius: 12px; padding: 12px 12px 12px 16px; transition: border-color .15s ease, box-shadow .15s ease; }
                .ex-row:hover { border-color: var(--c); box-shadow: 0 4px 14px rgba(18, 38, 58, .08); }
                .ex-row:focus-within { outline: 2px solid var(--c); outline-offset: 2px; }
                .ex-ico { flex: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
                .ex-ico svg { width: 21px; height: 21px; fill: currentColor; }
                .ex-text { flex: 1; min-width: 0; }
                .ex-name { display: flex; align-items: center; gap: 8px; min-width: 0; }
                .ex-name a { font-size: 15px; font-weight: 600; color: #12263a; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .ex-name a::after { content: ''; position: absolute; inset: 0; border-radius: 12px; z-index: 1; }
                .ex-tag { flex: none; font: 600 11px/1 ui-monospace, 'IBM Plex Mono', monospace; letter-spacing: .04em; text-transform: uppercase; color: var(--c); background: color-mix(in srgb, var(--c) 12%, #fff); padding: 5px 8px; border-radius: 6px; }
                .ex-meta { display: block; margin-top: 3px; font-size: 13px; color: #607282; }
                .ex-go { color: #9aa9b6; font-size: 18px; }
                .ex-row:hover .ex-go { color: var(--c); }
                .ex-menu { position: relative; z-index: 3; }
                .ex-menu > summary { list-style: none; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #607282; }
                .ex-menu > summary::-webkit-details-marker { display: none; }
                .ex-menu > summary:hover, .ex-menu[open] > summary { background: #eef2f6; color: #12263a; }
                .ex-menu svg { width: 20px; height: 20px; fill: currentColor; }
                .ex-menu-list { position: absolute; right: 0; top: 38px; min-width: 170px; background: #fff; border: 1px solid #dde4ea; border-radius: 10px; box-shadow: 0 8px 20px rgba(16, 40, 63, .14); padding: 6px 0; z-index: 5; }
                .ex-menu-list a, .ex-menu-list button { display: block; width: 100%; text-align: left; appearance: none; border: 0; background: transparent; font-size: 14px; font-weight: 500; font-family: inherit; color: #12263a; padding: 9px 14px; text-decoration: none; cursor: pointer; }
                .ex-menu-list a:hover, .ex-menu-list button:hover { background: #f3f6f9; }
                .ex-menu-list form { margin: 0; }
                .ex-menu-list .ex-del { color: #b03a3a; }
                .ex-empty { text-align: center; padding: 56px 16px; border: 1px dashed #c9d3dc; border-radius: 14px; background: #fff; color: #607282; margin-top: 24px; }
                .ex-empty h2 { font-size: 20px; color: #12263a; margin: 0 0 8px; }
                .ex-empty p { margin: 0 0 18px; font-size: 14px; }
                @media (max-width: 600px) { .ex-head h1 { font-size: 24px; } .ex-meta { line-height: 1.5; } }
                @media (prefers-reduced-motion: reduce) { .ex-row { transition: none; } }
            </style>

            <div class="ex">
                <a href="{{ route('courses.show', $course) }}" class="ex-back">← Back to {{ $course->courseCode }}</a>

                <header class="ex-head">
                    <div>
                        <p class="ex-eyebrow">{{ $course->courseCode }} <i>/</i> Exams and quizzes</p>
                        <h1>Exams</h1>
                        <p>Review exams generated from a Table of Specifications and quizzes built without one. Open one to replace questions, then format and print.</p>
                    </div>
                    <div class="ex-actions">
                        <a href="{{ route('courses.tos.index', $course) }}" class="ex-btn">Go to ToS</a>
                        <a href="{{ route('courses.quizzes.create', $course) }}" class="ex-btn ex-btn-main">Create a quiz <span aria-hidden="true">+</span></a>
                    </div>
                </header>

                @if (session('status'))
                    <div class="work-notice" role="status" style="margin-top:16px;">{{ session('status') }}</div>
                @endif

                @if ($exams->isEmpty())
                    <div class="ex-empty">
                        <h2>No exams yet</h2>
                        <p>Open a Table of Specifications and choose "Generate exam," or create a quiz without one.</p>
                        <div class="ex-actions" style="justify-content:center;">
                            <a href="{{ route('courses.tos.index', $course) }}" class="ex-btn">Go to ToS</a>
                            <a href="{{ route('courses.quizzes.create', $course) }}" class="ex-btn ex-btn-main">Create a quiz <span aria-hidden="true">+</span></a>
                        </div>
                    </div>
                @else
                    <div class="ex-bar">
                        <strong>{{ $exams->count() }} {{ $exams->count() === 1 ? 'exam' : 'exams' }}</strong>
                        <span>Newest first</span>
                    </div>
                    <ul class="ex-list">
                        @foreach ($exams as $exam)
                            <li class="ex-row">
                                <span class="ex-ico" aria-hidden="true"><svg viewBox="0 0 24 24">@if ($exam->isQuiz())<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-6 15h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 11.9 13 12.5 13 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/>@else<path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/>@endif</svg></span>
                                <span class="ex-text">
                                    <span class="ex-name">
                                        <a href="{{ route('courses.exams.show', [$course, $exam]) }}">{{ $exam->tosTitle }}</a>
                                        <span class="ex-tag">{{ $exam->kind }}</span>
                                    </span>
                                    <span class="ex-meta">{{ $exam->totalItems }} {{ $exam->totalItems == 1 ? 'item' : 'items' }} · {{ \App\Support\Points::format($exam->totalScore) }} · {{ \Illuminate\Support\Carbon::parse($exam->created)->format('M j, Y') }}</span>
                                </span>
                                <span class="ex-go" aria-hidden="true">›</span>
                                <details class="ex-menu">
                                    <summary aria-label="More options for {{ $exam->tosTitle }}" title="More options"><svg viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg></summary>
                                    <div class="ex-menu-list">
                                        <a href="{{ route('courses.exams.show', [$course, $exam]) }}">View</a>
                                        <a href="{{ route('courses.exams.format', [$course, $exam]) }}">Format and print</a>
                                        <a href="{{ route('courses.exams.answerkey', [$course, $exam]) }}">Answer key</a>
                                        <form method="POST" action="{{ route('courses.exams.destroy', [$course, $exam]) }}" data-confirm="Delete this exam?" data-confirm-item="{{ $exam->tosTitle }} · {{ $exam->created }}" data-confirm-label="Delete exam">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ex-del">Delete</button>
                                        </form>
                                    </div>
                                </details>
                            </li>
                        @endforeach
                    </ul>
                    <script>
                        document.addEventListener('click', function (e) {
                            document.querySelectorAll('.ex-menu[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
                        });
                    </script>
                @endif
            </div>

        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
