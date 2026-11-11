<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $course->courseCode }} · {{ $course->courseName }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
    <style>
        .dp-back { display: inline-block; margin: 4px 0 16px; font-size: 14px; font-weight: 600; color: #2a5375; text-decoration: none; }
        .dp-back:hover { color: #12263a; text-decoration: underline; }
        .dp-head { border-left: 8px solid var(--c); background: #fff; border-top: 1px solid #dde4ea; border-right: 1px solid #dde4ea; border-bottom: 1px solid #dde4ea; border-radius: 4px 14px 14px 4px; padding: 22px 26px; }
        .dp-code { display: inline-block; font: 600 12px/1 ui-monospace, 'IBM Plex Mono', monospace; letter-spacing: .04em; color: var(--c); background: color-mix(in srgb, var(--c) 12%, #fff); padding: 6px 9px; border-radius: 6px; }
        .dp-head h1 { font-size: 30px; line-height: 1.2; color: #12263a; margin: 12px 0 4px; letter-spacing: -.01em; }
        .dp-head p { margin: 0; font-size: 14px; color: #607282; }
        .dp-layout { display: grid; grid-template-columns: 210px minmax(0, 1fr); gap: 26px; margin-top: 24px; align-items: start; }
        .dp-side { background: color-mix(in srgb, var(--c) 7%, #fff); border-radius: 14px; padding: 16px 18px; }
        .dp-side h2 { font-size: 12px; letter-spacing: .08em; text-transform: uppercase; color: #46607a; margin: 0 0 8px; }
        .dp-side dl { margin: 0; }
        .dp-stat { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; padding: 7px 0; }
        .dp-side dt { font-size: 14px; color: #3c556e; }
        .dp-side dd { margin: 0; font-size: 18px; font-weight: 700; color: #12263a; }
        .dp-group { margin: 0 0 26px; }
        .dp-group h2 { display: flex; align-items: center; gap: 10px; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; color: #46607a; margin: 0 0 10px; }
        .dp-group h2::before { content: ''; width: 18px; height: 3px; border-radius: 2px; background: var(--c); }
        .dp-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
        .dp-row { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #dde4ea; border-radius: 12px; padding: 13px 16px; text-decoration: none; color: inherit; transition: border-color .15s ease, box-shadow .15s ease; }
        .dp-row:hover { border-color: var(--c); box-shadow: 0 4px 14px rgba(18, 38, 58, .08); }
        .dp-row:focus-visible { outline: 2px solid var(--c); outline-offset: 2px; }
        .dp-ico { flex: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--c) 12%, #fff); color: var(--c); }
        .dp-ico svg { width: 21px; height: 21px; fill: currentColor; }
        .dp-text { flex: 1; min-width: 0; }
        .dp-text strong { display: block; font-size: 15px; color: #12263a; }
        .dp-text span { display: block; font-size: 13px; color: #607282; margin-top: 2px; }
        .dp-go { color: #9aa9b6; font-size: 18px; }
        .dp-row:hover .dp-go { color: var(--c); }
        @media (max-width: 760px) { .dp-layout { grid-template-columns: 1fr; } .dp-head h1 { font-size: 24px; } }
        @media (prefers-reduced-motion: reduce) { .dp-row { transition: none; } }
    </style>
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        @php $color = \App\Support\CourseTheme::color($course); @endphp
        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><span aria-current="page">{{ $course->courseCode }}</span>
            </nav>

            <div class="dp" style="--c: {{ $color }};">
                <a href="{{ route('courses.index') }}" class="dp-back">← Back to course list</a>

                <header class="dp-head">
                    <span class="dp-code">{{ $course->courseCode }}</span>
                    <h1>{{ $course->courseName }}</h1>
                    <p>{{ $course->creditUnits !== null ? \App\Support\TosHeading::units($course->creditUnits) : 'Credit units not set' }}</p>
                </header>

                @if (session('status'))
                    <div class="work-notice" role="status" style="margin-top:16px;">{{ session('status') }}</div>
                @endif

                <div class="dp-layout">
                    <aside class="dp-side" aria-label="Course summary">
                        <h2>Summary</h2>
                        <dl>
                            <div class="dp-stat"><dt>Topics</dt><dd>{{ $stats['topics'] }}</dd></div>
                            <div class="dp-stat"><dt>Questions</dt><dd>{{ $stats['questions'] }}</dd></div>
                            <div class="dp-stat"><dt>ToS plans</dt><dd>{{ $stats['tos'] }}</dd></div>
                            <div class="dp-stat"><dt>Exams</dt><dd>{{ $stats['exams'] }}</dd></div>
                        </dl>
                    </aside>

                    <div>
                        <section class="dp-group" aria-labelledby="dp-bank">
                            <h2 id="dp-bank">Question bank</h2>
                            <ul class="dp-list">
                    <li><a href="{{ route('courses.topics.index', $course) }}" class="dp-row"><span class="dp-ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H8V4h12v12z"/></svg></span><span class="dp-text"><strong>View question bank</strong><span>Browse topics and the questions under each one</span></span><span class="dp-go" aria-hidden="true">›</span></a></li>
                            </ul>
                        </section>

                        <section class="dp-group" aria-labelledby="dp-exams">
                            <h2 id="dp-exams">Exams</h2>
                            <ul class="dp-list">
                    <li><a href="{{ route('courses.tos.create', $course) }}" class="dp-row"><span class="dp-ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg></span><span class="dp-text"><strong>Create a ToS</strong><span>Plan a Midterm or Final, then generate the exam from it</span></span><span class="dp-go" aria-hidden="true">›</span></a></li>
                    <li><a href="{{ route('courses.tos.index', $course) }}" class="dp-row"><span class="dp-ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 3v18h18V3H3zm8 16H5v-6h6v6zm0-8H5V5h6v6zm8 8h-6v-6h6v6zm0-8h-6V5h6v6z"/></svg></span><span class="dp-text"><strong>Tables of Specification</strong><span>View, edit, print, or generate an exam from a ToS</span></span><span class="dp-go" aria-hidden="true">›</span></a></li>
                    <li><a href="{{ route('courses.quizzes.create', $course) }}" class="dp-row"><span class="dp-ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-6 15h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 11.9 13 12.5 13 14h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z"/></svg></span><span class="dp-text"><strong>Create a quiz</strong><span>No ToS needed. Just a title, topics, and item counts</span></span><span class="dp-go" aria-hidden="true">›</span></a></li>
                    <li><a href="{{ route('courses.exams.index', $course) }}" class="dp-row"><span class="dp-ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg></span><span class="dp-text"><strong>Exams and quizzes</strong><span>Review, replace questions, format, and print</span></span><span class="dp-go" aria-hidden="true">›</span></a></li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
