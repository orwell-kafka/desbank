<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create ToS - {{ $course->courseName }} · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.tos.index', $course) }}">{{ $course->courseName }} ToS</a><span aria-hidden="true">/</span><span aria-current="page">New ToS</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> <span class="work-course-code">{{ $course->courseCode }}</span> Table of Specifications</p>
                    <h1>Create a ToS<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Plan how many items each topic and Bloom level gets, then generate an exam from it.</p>
                </div>
            </header>

            @if ($topics->isEmpty())
                <div class="work-directory" style="margin-top:29px;">
                    <div class="work-empty">
                        <span class="work-empty-number" aria-hidden="true">00</span>
                        <h3>This course has no topics yet.</h3>
                        <p>Add topics before creating a ToS. Table 1 lists the course topics and their contact hours.</p>
                        <a href="{{ route('courses.topics.create', $course) }}" class="work-action-primary">Add a topic <span aria-hidden="true">+</span></a>
                    </div>
                </div>
            @else
            @php
                // After a failed validation the submitted values come back; show those, not the saved ones.
                $hasOld = session()->hasOldInput();
                $oldTopicIds = array_map('strval', (array) old('topicId', []));
                $oldHours = (array) old('contactHours', []);
                $initialRows = [];
                if ($hasOld) {
                    $oldPoints = (array) old('pointsPerItem', []);
                    $oldCounts = (array) old('itemCount', []);
                    foreach ((array) old('bloomKey', []) as $key) {
                        [$rowLevel, $rowType] = array_pad(explode('|', (string) $key, 2), 2, '');
                        $initialRows[] = ['level' => $rowLevel, 'type' => $rowType, 'points' => (float) ($oldPoints[$key] ?? 0), 'count' => (int) ($oldCounts[$key] ?? 0)];
                    }
                }
            @endphp
            <div class="work-form-layout work-form-layout-wide">
                <form method="POST" action="{{ route('courses.tos.store', $course) }}" class="work-form" data-pending>
                    @csrf
                    @include('partials.work-errors')

                    <section class="work-section" aria-labelledby="tf-details">
                        <div class="work-section-head">
                            <span class="work-section-num" aria-hidden="true">01</span>
                            <div><h2 id="tf-details">Details</h2><p>Name the plan and set the exam type and total score.</p></div>
                        </div>
                        <div class="work-section-body">
                            <div class="work-field">
                                <label class="work-label" for="tosTitle">ToS title</label>
                                <input type="text" id="tosTitle" name="title" class="work-input {{ $errors->has('title') ? 'is-invalid' : '' }}" value="{{ old('title') }}" required @if ($errors->has('title')) aria-invalid="true" @endif>
                            </div>
                            <div class="work-field-row">
                                <div class="work-field">
                                    <label class="work-label" for="examType">Exam type</label>
                                    <select name="examType" id="examType" class="work-select {{ $errors->has('examType') ? 'is-invalid' : '' }}" required>
                                        <option value="">-- Select --</option>
                                        @foreach ($examTypes as $type)
                                            <option value="{{ $type }}" {{ old('examType') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="work-field">
                                    <label class="work-label" for="totalScore">Total score</label>
                                    <input type="number" step="0.01" id="totalScore" name="totalScore" class="work-input {{ $errors->has('totalScore') ? 'is-invalid' : '' }}" value="{{ old('totalScore') }}" required @if ($errors->has('totalScore')) aria-invalid="true" @endif>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="work-section" aria-labelledby="tf-topics">
                        <div class="work-section-head">
                            <span class="work-section-num" aria-hidden="true">02</span>
                            <div><h2 id="tf-topics">Table 1 — Topics &amp; contact hours</h2><p>Check each topic covered by this exam and enter its contact hours.</p></div>
                        </div>
                        <div class="work-section-body">
                            <div class="work-table-scroll work-table-framed {{ $errors->hasAny(['topicId', 'contactHours']) ? 'is-invalid' : '' }}" style="margin-top:0;" role="region" aria-label="Topics and contact hours" tabindex="0" data-error-key="topicId contactHours">
                                <table class="work-table work-table-inset">
                                    <thead><tr>
                                        <th scope="col" class="work-check-col">Include</th>
                                        <th scope="col">Topic</th>
                                        <th scope="col" class="work-num">Contact hours</th>
                                    </tr></thead>
                                    <tbody>
                                        @foreach ($topics as $topic)
                                            @php
                                                $isChecked = $hasOld && in_array((string) $topic->topicId, $oldTopicIds, true);
                                                $hours = $oldHours[$topic->topicId] ?? 0;
                                            @endphp
                                            <tr>
                                                <td><input type="checkbox" class="work-check" id="topic-{{ $topic->topicId }}" name="topicId[]" value="{{ $topic->topicId }}" {{ $isChecked ? 'checked' : '' }}></td>
                                                <td class="work-course-name"><label for="topic-{{ $topic->topicId }}" style="cursor:pointer;">{{ $topic->topicName }}</label></td>
                                                <td class="work-num"><input type="number" step="0.5" min="0" class="work-input work-hours hours-input" name="contactHours[{{ $topic->topicId }}]" value="{{ $hours }}" aria-label="Contact hours for {{ $topic->topicName }}"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="work-section" aria-labelledby="tf-bloom">
                        <div class="work-section-head">
                            <span class="work-section-num" aria-hidden="true">03</span>
                            <div><h2 id="tf-bloom">Table 2 — Bloom's taxonomy distribution</h2><p>Pick a Bloom level, question type, points per item, and item count, then add the row. The total score across all rows must equal the Total Score above.</p></div>
                        </div>
                        <div class="work-section-body">
                            <div class="work-score-summary" id="scoreSummary" aria-label="Score summary">
                                <div class="work-score-stat"><span class="work-score-label">Total score</span><strong id="scoreAccumulated">0.00</strong><small>added so far</small></div>
                                <div class="work-score-stat"><span class="work-score-label">Target score</span><strong id="scoreTarget">&mdash;</strong><small>from Details</small></div>
                                <div class="work-score-stat"><span class="work-score-label">Remaining</span><strong id="scoreRemaining">&mdash;</strong><small id="scoreRemainingNote">enter a target</small></div>
                                <div class="work-score-stat"><span class="work-score-label">Items</span><strong id="scoreItems">0</strong><small>questions planned</small></div>
                                <div class="work-score-bar" id="scoreBar" role="progressbar" aria-label="Progress toward target score" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span id="scoreBarFill"></span></div>
                            </div>
                            <div class="work-builder {{ $errors->has('bloomKey') ? 'is-invalid' : '' }}" data-error-key="bloomKey itemCount pointsPerItem" role="group" aria-label="Add a distribution row">
                                <div class="work-field">
                                    <label class="work-label" for="newBloomLevel">Bloom level</label>
                                    <select id="newBloomLevel" class="work-select">
                                        @foreach ($bloomLevels as $level)
                                            <option value="{{ $level }}">{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="work-field">
                                    <label class="work-label" for="newQuestionType">Question type</label>
                                    <select id="newQuestionType" class="work-select">
                                        @foreach ($questionTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="work-field">
                                    <label class="work-label" for="newPoints">Points/item</label>
                                    <input type="number" step="0.5" min="0" id="newPoints" value="1" class="work-input">
                                </div>
                                <div class="work-field">
                                    <label class="work-label" for="newCount">Item count</label>
                                    <input type="number" min="0" id="newCount" value="1" class="work-input">
                                </div>
                                <div class="work-builder-add">
                                    <button type="button" id="addBloomRowBtn" class="work-action-primary">Add row <span aria-hidden="true">+</span></button>
                                </div>
                            </div>
                            <p id="bloomRowError" class="work-builder-error" role="alert" hidden></p>

                            <div class="work-table-scroll work-table-framed" role="region" aria-label="Bloom distribution rows" tabindex="0">
                                <table class="work-table work-table-inset work-table-bloom" id="bloomRowsTable" data-initial="{{ json_encode($initialRows) }}">
                                    <thead><tr>
                                        <th scope="col">Bloom level</th>
                                        <th scope="col">Question type</th>
                                        <th scope="col" class="work-num">Points/item</th>
                                        <th scope="col" class="work-num">Item count</th>
                                        <th scope="col" class="work-num">Score</th>
                                        <th scope="col"><span class="sr-only" style="position:absolute;left:-9999px;">Remove</span></th>
                                    </tr></thead>
                                    <tbody id="bloomRowsBody"></tbody>
                                </table>
                            </div>
                            <div class="work-total">
                                <span>Table 2 total <strong id="bloomRunningTotal">0.00</strong></span>
                                <span id="bloomTotalStatus" class="work-status" role="status" aria-live="polite"></span>
                            </div>
                            <div id="bloomHiddenInputs"></div>
                        </div>
                    </section>

                    <div class="work-form-actions">
                        <button type="submit" class="work-action-primary" data-pending-label="Saving…">Save ToS <span aria-hidden="true">→</span></button>
                        <a href="{{ route('courses.tos.index', $course) }}" class="work-form-cancel">Cancel</a>
                    </div>
                </form>
            </div>
            <script src="{{ asset('js/tos-form.js') }}?v={{ @filemtime(public_path('js/tos-form.js')) }}"></script>
            @endif
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>
</body>
</html>
