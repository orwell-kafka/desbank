<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create a quiz - {{ $course->courseName }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.show', $course) }}">{{ $course->courseName }}</a><span aria-hidden="true">/</span><span aria-current="page">Create a quiz</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Quiz</p>
                    <h1>Create a quiz<span class="work-title-period">.</span></h1>
                    <p class="work-intro">No Table of Specifications needed. Name the quiz, choose the topics, and say how many items of each type you want. Questions are picked at random from your question bank.</p>
                </div>
            </header>

            @if ($topics->isEmpty())
                <div class="work-directory" style="margin-top:29px;">
                    <div class="work-empty">
                        <span class="work-empty-number" aria-hidden="true">00</span>
                        <h3>This course has no topics yet.</h3>
                        <p>Add a topic and some questions first, then come back to build a quiz.</p>
                        <a href="{{ route('courses.topics.create', $course) }}" class="work-action-primary">Add a topic <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            @else
                <div class="work-form-layout">
                    <form method="POST" action="{{ route('courses.quizzes.store', $course) }}" class="work-form" data-pending>
                        @csrf
                        @include('partials.work-errors')

                        <section class="work-section work-section-flush" aria-label="Quiz title">
                            <div class="work-section-body">
                                <div class="work-field">
                                    <label class="work-label" for="title">Quiz title</label>
                                    <input type="text" id="title" name="title" class="work-input {{ $errors->has('title') ? 'is-invalid' : '' }}" value="{{ old('title') }}" placeholder="e.g. Quiz 1: Systems Development" maxlength="150" required autofocus @if ($errors->has('title')) aria-invalid="true" @endif>
                                </div>
                            </div>
                        </section>

                        <section class="work-section" aria-labelledby="qz-topics">
                            <div class="work-section-head">
                                <span class="work-section-num" aria-hidden="true">01</span>
                                <div><h2 id="qz-topics">Topics</h2><p>Questions are drawn only from the topics you tick. The "In bank" counts below follow your selection.</p></div>
                            </div>
                            <div class="work-section-body">
                                @foreach ($topics as $topic)
                                    <label style="display:flex;align-items:center;gap:10px;padding:6px 0;">
                                        <input type="checkbox" class="work-check qz-topic" name="topicId[]" value="{{ $topic->topicId }}" {{ in_array($topic->topicId, old('topicId', $topics->pluck('topicId')->all())) ? 'checked' : '' }}>
                                        <span>{{ $topic->topicName }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </section>

                        <section class="work-section" aria-labelledby="qz-items">
                            <div class="work-section-head">
                                <span class="work-section-num" aria-hidden="true">02</span>
                                <div><h2 id="qz-items">Items</h2><p>Leave a type at 0 to skip it.</p></div>
                            </div>
                            <div class="work-section-body">
                                <div class="work-table-scroll" role="region" aria-label="Items per question type" tabindex="0">
                                    <table class="work-table work-table-narrow">
                                        <thead><tr>
                                            <th scope="col">Question type</th>
                                            <th scope="col" class="work-num">In bank</th>
                                            <th scope="col" class="work-num">Items</th>
                                            <th scope="col" class="work-num">Points each</th>
                                        </tr></thead>
                                        <tbody>
                                            @foreach ($types as $type)
                                                <tr>
                                                    <td>{{ $type }}</td>
                                                    <td class="work-num"><span class="qz-avail" data-type="{{ $type }}">0</span></td>
                                                    <td class="work-num"><input type="number" class="work-input qz-count" data-type="{{ $type }}" name="itemCount[{{ $type }}]" value="{{ old("itemCount.$type", 0) }}" min="0" max="200" style="width:84px;" aria-label="Items of {{ $type }}"></td>
                                                    <td class="work-num"><input type="number" class="work-input qz-points" name="pointsPerItem[{{ $type }}]" value="{{ old("pointsPerItem.$type", 1) }}" min="0.5" max="100" step="0.5" style="width:84px;" aria-label="Points per {{ $type }} item"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot><tr>
                                            <td><strong>Total</strong></td>
                                            <td></td>
                                            <td class="work-num"><strong id="qzTotalItems">0</strong></td>
                                            <td class="work-num"><strong id="qzTotalScore">0</strong> pts</td>
                                        </tr></tfoot>
                                    </table>
                                </div>
                            </div>
                        </section>

                        <div class="work-form-actions">
                            <button type="submit" class="work-action-primary" data-pending-label="Creating…">Create quiz <span aria-hidden="true">→</span></button>
                            <a href="{{ route('courses.show', $course) }}" class="work-form-cancel">Cancel</a>
                        </div>
                    </form>

                    <aside class="work-aside" aria-label="About quizzes">
                        <h2>How quizzes work</h2>
                        <ol>
                            <li><span class="work-step-num">01</span><div><strong>No ToS</strong><p>Quizzes skip the Table of Specifications. Only major exams need one.</p></div></li>
                            <li><span class="work-step-num">02</span><div><strong>Random picks</strong><p>Questions are chosen at random from the ticked topics, any Bloom level.</p></div></li>
                            <li><span class="work-step-num">03</span><div><strong>Same tools</strong><p>Review, replace questions, format, print, and get an answer key from Exams.</p></div></li>
                        </ol>
                    </aside>
                </div>
                <script>
                    (function () {
                        var byTopic = @json($availableByTopic);
                        var topics = document.querySelectorAll('.qz-topic');
                        var rows = document.querySelectorAll('.qz-count');
                        var points = document.querySelectorAll('.qz-points');
                        var avail = {};

                        function recount() {
                            avail = {};
                            topics.forEach(function (t) {
                                if (!t.checked) return;
                                var per = byTopic[t.value] || {};
                                Object.keys(per).forEach(function (type) { avail[type] = (avail[type] || 0) + per[type]; });
                            });
                            document.querySelectorAll('.qz-avail').forEach(function (el) { el.textContent = avail[el.getAttribute('data-type')] || 0; });
                        }

                        function update() {
                            var items = 0, score = 0;
                            rows.forEach(function (c, i) {
                                var type = c.getAttribute('data-type');
                                var have = avail[type] || 0;
                                var n = parseInt(c.value, 10) || 0, p = parseFloat(points[i].value) || 0;
                                c.setAttribute('max', have);
                                var over = n > have;
                                c.style.borderColor = over ? '#b03a3a' : '';
                                c.title = over ? 'Only ' + have + ' available in the selected topics' : '';
                                items += n; score += n * p;
                            });
                            document.getElementById('qzTotalItems').textContent = items;
                            document.getElementById('qzTotalScore').textContent = Math.round(score * 100) / 100;
                        }

                        topics.forEach(function (t) { t.addEventListener('change', function () { recount(); update(); }); });
                        document.querySelectorAll('.qz-count, .qz-points').forEach(function (el) { el.addEventListener('input', update); });
                        recount(); update();
                    })();
                </script>
            @endif

            <div class="work-below-directory"><a href="{{ route('courses.show', $course) }}">← Back to {{ $course->courseCode }}</a><span>Quiz</span></div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
