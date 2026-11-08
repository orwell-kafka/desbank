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
                <a href="{{ route('dashboard.instructor') }}">Instructor workspace</a><span aria-hidden="true">/</span><a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.show', $course) }}">{{ $course->courseName }}</a><span aria-hidden="true">/</span><span aria-current="page">Topics</span>
            </nav>
            @php
                $color = \App\Support\CourseTheme::color($course);
                $totalQuestions = $questionCounts->sum();
            @endphp
            <style>
                .tp { --c: {{ $color }}; }
                .tp-head { background: #fff; border: 1px solid #dde4ea; border-top: 6px solid var(--c); border-radius: 14px; padding: 20px 26px 22px; display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
                .tp-eyebrow { display: flex; align-items: center; gap: 8px; margin: 0; font: 600 12px/1 ui-monospace, 'IBM Plex Mono', monospace; letter-spacing: .06em; text-transform: uppercase; color: var(--c); }
                .tp-eyebrow i { font-style: normal; color: #9aa9b6; }
                .tp-head h1 { font-size: 30px; line-height: 1.2; color: #12263a; margin: 12px 0 6px; letter-spacing: -.01em; }
                .tp-head p { margin: 0; font-size: 14px; color: #607282; max-width: 520px; }
                .tp-add { display: inline-flex; align-items: center; gap: 8px; background: var(--c); color: #fff; text-decoration: none; font-weight: 600; font-size: 14px; padding: 10px 18px; border-radius: 999px; }
                .tp-add:hover { filter: brightness(1.12); }
                .tp-bar { display: flex; align-items: center; justify-content: space-between; margin: 24px 0 10px; font-size: 13px; color: #607282; }
                .tp-bar strong { font-size: 13px; letter-spacing: .08em; text-transform: uppercase; color: #46607a; }
                .tp-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
                .tp-row { position: relative; display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid #dde4ea; border-radius: 12px; padding: 12px 12px 12px 16px; transition: border-color .15s ease, box-shadow .15s ease; }
                .tp-row:hover { border-color: var(--c); box-shadow: 0 4px 14px rgba(18, 38, 58, .08); }
                .tp-row:focus-within { outline: 2px solid var(--c); outline-offset: 2px; }
                .tp-num { flex: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font: 600 13px ui-monospace, 'IBM Plex Mono', monospace; color: var(--c); background: color-mix(in srgb, var(--c) 12%, #fff); }
                .tp-text { flex: 1; min-width: 0; }
                .tp-name { display: block; font-size: 15px; font-weight: 600; color: #12263a; text-decoration: none; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .tp-name::after { content: ''; position: absolute; inset: 0; border-radius: 12px; z-index: 1; }
                .tp-count { display: block; margin-top: 2px; font-size: 13px; color: #607282; }
                .tp-go { color: #9aa9b6; font-size: 18px; }
                .tp-row:hover .tp-go { color: var(--c); }
                .tp-menu { position: relative; z-index: 3; }
                .tp-menu > summary { list-style: none; width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #607282; }
                .tp-menu > summary::-webkit-details-marker { display: none; }
                .tp-menu > summary:hover, .tp-menu[open] > summary { background: #eef2f6; color: #12263a; }
                .tp-menu svg { width: 20px; height: 20px; fill: currentColor; }
                .tp-menu-list { position: absolute; right: 0; top: 38px; min-width: 150px; background: #fff; border: 1px solid #dde4ea; border-radius: 10px; box-shadow: 0 8px 20px rgba(16, 40, 63, .14); padding: 6px 0; z-index: 5; }
                .tp-menu-list a, .tp-menu-list button { display: block; width: 100%; text-align: left; appearance: none; border: 0; background: transparent; font: 500 14px inherit; font-family: inherit; color: #12263a; padding: 9px 14px; text-decoration: none; cursor: pointer; }
                .tp-menu-list a:hover, .tp-menu-list button:hover { background: #f3f6f9; }
                .tp-menu-list form { margin: 0; }
                .tp-menu-list .tp-del { color: #b03a3a; }
                .tp-empty { text-align: center; padding: 56px 16px; border: 1px dashed #c9d3dc; border-radius: 14px; background: #fff; color: #607282; }
                .tp-empty h2 { font-size: 20px; color: #12263a; margin: 0 0 8px; }
                .tp-empty p { margin: 0 0 18px; font-size: 14px; }
                @media (max-width: 600px) { .tp-head h1 { font-size: 24px; } }
                @media (prefers-reduced-motion: reduce) { .tp-row { transition: none; } }
            </style>

            <div class="tp">
                <a href="{{ route('courses.show', $course) }}" class="dp-back" style="display:inline-block;margin:4px 0 16px;font-size:14px;font-weight:600;color:#2a5375;text-decoration:none;">← Back to {{ $course->courseCode }}</a>

                <header class="tp-head">
                    <div>
                        <p class="tp-eyebrow">{{ $course->courseCode }} <i>/</i> Question bank</p>
                        <h1>Topics</h1>
                        <p>Questions are organized by topic. Open a topic to add or review its questions for <strong>{{ $course->courseName }}</strong>.</p>
                    </div>
                    <a href="{{ route('courses.topics.create', $course) }}" class="tp-add">Add a topic <span aria-hidden="true">+</span></a>
                </header>

                @if (session('status'))
                    <div class="work-notice" role="status" style="margin-top:16px;">{{ session('status') }}</div>
                @endif

                @if ($topics->isEmpty())
                    <div class="tp-empty" style="margin-top:24px;">
                        <h2>No topics yet</h2>
                        <p>Add the first topic for this course. Each topic holds its own questions, and a Table of Specifications needs at least one.</p>
                        <a href="{{ route('courses.topics.create', $course) }}" class="tp-add">Add the first topic <span aria-hidden="true">+</span></a>
                    </div>
                @else
                    <div class="tp-bar">
                        <strong>{{ $topics->count() }} {{ $topics->count() === 1 ? 'topic' : 'topics' }}</strong>
                        <span>{{ $totalQuestions }} {{ $totalQuestions == 1 ? 'question' : 'questions' }} in total</span>
                    </div>
                    <ul class="tp-list">
                        @foreach ($topics as $topic)
                            @php $qc = (int) ($questionCounts[$topic->topicId] ?? 0); @endphp
                            <li class="tp-row">
                                <span class="tp-num" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="tp-text">
                                    <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}" class="tp-name">{{ $topic->topicName }}</a>
                                    <span class="tp-count">{{ $qc }} {{ $qc === 1 ? 'question' : 'questions' }}</span>
                                </span>
                                <span class="tp-go" aria-hidden="true">›</span>
                                <details class="tp-menu">
                                    <summary aria-label="More options for {{ $topic->topicName }}" title="More options"><svg viewBox="0 0 24 24"><path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/></svg></summary>
                                    <div class="tp-menu-list">
                                        <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}">Open questions</a>
                                        <a href="{{ route('courses.topics.edit', [$course, $topic]) }}">Edit</a>
                                        <form method="POST" action="{{ route('courses.topics.destroy', [$course, $topic]) }}" data-confirm="Delete this topic?" data-confirm-item="{{ $topic->topicName }}" data-confirm-label="Delete topic">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tp-del">Delete</button>
                                        </form>
                                    </div>
                                </details>
                            </li>
                        @endforeach
                    </ul>
                    <script>
                        document.addEventListener('click', function (e) {
                            document.querySelectorAll('.tp-menu[open]').forEach(function (d) { if (!d.contains(e.target)) d.removeAttribute('open'); });
                        });
                    </script>
                @endif
            </div>

        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
