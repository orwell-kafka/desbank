<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Question · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.topics.index', $course) }}">{{ $course->courseName }}</a><span aria-hidden="true">/</span><a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}">{{ $topic->topicName }}</a><span aria-hidden="true">/</span><span aria-current="page">New question</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Question bank</p>
                    <h1>Add a question<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Add an item to the question bank for <strong>{{ $topic->topicName }}</strong> in {{ $course->courseName }}.</p>
                </div>
            </header>

            <div class="work-form-layout">
                <form method="POST" action="{{ route('courses.topics.questions.store', [$course, $topic]) }}" class="work-form" id="questionForm" data-pending>
                    @csrf
                    @include('partials.work-errors')

                    <section class="work-section" aria-labelledby="qf-class">
                        <div class="work-section-head">
                            <span class="work-section-num" aria-hidden="true">01</span>
                            <div><h2 id="qf-class">Classification</h2><p>How this item is used when a Table of Specifications is filled.</p></div>
                        </div>
                        <div class="work-section-body">
                            <div class="work-field-row">
                                <div class="work-field">
                                    <label class="work-label" for="bloomLevel">Bloom's taxonomy level</label>
                                    <select name="bloomLevel" id="bloomLevel" required class="work-select {{ $errors->has('bloomLevel') ? 'is-invalid' : '' }}">
                                        <option value="">-- Select --</option>
                                        @foreach ($bloomLevels as $level)
                                            <option value="{{ $level }}" {{ old('bloomLevel') == $level ? 'selected' : '' }}>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="work-field">
                                    <label class="work-label" for="questionType">Question type</label>
                                    <select name="questionType" id="questionType" required class="work-select {{ $errors->has('questionType') ? 'is-invalid' : '' }}">
                                        <option value="">-- Select --</option>
                                        @foreach ($questionTypes as $type)
                                            <option value="{{ $type }}" {{ old('questionType') == $type ? 'selected' : '' }}>{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="work-section" aria-labelledby="qf-question">
                        <div class="work-section-head">
                            <span class="work-section-num" aria-hidden="true">02</span>
                            <div><h2 id="qf-question">Question</h2><p>Write the item as it should appear on the exam.</p></div>
                        </div>
                        <div class="work-section-body">
                            <div class="work-field">
                                <label class="work-label" for="content">Question text</label>
                                <textarea name="content" id="content" rows="4" required class="work-textarea {{ $errors->has('content') ? 'is-invalid' : '' }}">{{ old('content') }}</textarea>
                            </div>
                            <div id="blankTools" class="work-blank-tools" role="group" aria-label="Blanks and answer lines">
                                <span class="work-blank-tools-label">Blanks &amp; answer lines</span>
                                <div class="work-blank-tools-row">
                                    <button type="button" class="work-row-btn work-row-btn-primary" data-blank="cursor">Insert blank at cursor</button>
                                    <button type="button" class="work-row-btn" data-blank="start">Answer line at start</button>
                                    <button type="button" class="work-row-btn" data-blank="end">Answer line at end</button>
                                    <button type="button" class="work-row-btn" data-blank="clear">Remove blanks</button>
                                    <label class="work-blank-len">Length
                                        <select id="blankLength" class="work-select" aria-label="Blank length">
                                            <option value="6">Short</option>
                                            <option value="10" selected>Medium</option>
                                            <option value="16">Long</option>
                                        </select>
                                    </label>
                                </div>
                                <p id="blankSummary" class="work-hint" role="status" aria-live="polite"></p>
                                <p class="work-hint">Tip: select the answer word first, then press &ldquo;Insert blank at cursor&rdquo; to replace it with a blank.</p>
                            </div>
                        </div>
                    </section>

                    <section class="work-section" aria-labelledby="qf-answer">
                        <div class="work-section-head">
                            <span class="work-section-num" aria-hidden="true">03</span>
                            <div><h2 id="qf-answer">Answer</h2><p>The fields below change with the question type.</p></div>
                        </div>
                        <div class="work-section-body">
                            <div id="choicesField" class="work-dyn" data-error-key="choices">
                                <div class="work-field">
                                    <span class="work-label" id="choicesLabel">Answer choices</span>
                                    <ol id="choiceList" class="work-choice-list" aria-labelledby="choicesLabel" data-initial="{{ json_encode(array_values((array) (array) old('choices', []))) }}"></ol>
                                    <p id="choiceError" class="work-builder-error" role="alert" hidden></p>
                                    <button type="button" id="addChoiceBtn" class="work-row-btn work-row-btn-primary">+ Add answer</button>
                                    <p class="work-hint">One box per choice. Edit any box, or use Remove to delete it. No commas needed.</p>
                                </div>
                            </div>

                            <div id="answerTextField" class="work-dyn">
                                <div class="work-field">
                                    <label class="work-label" id="answerLabel" for="answerInput">Correct Answer</label>
                                    <input type="text" name="correctAnswer" id="answerInput" class="work-input" value="{{ old('correctAnswer') }}">
                                </div>
                            </div>

                            <div id="answerTrueFalseField" class="work-dyn" style="display:none;">
                                <div class="work-field">
                                    <label class="work-label" for="answerTrueFalseSelect">Correct Answer</label>
                                    <select id="answerTrueFalseSelect" class="work-select {{ $errors->has('correctAnswer') ? 'is-invalid' : '' }}" data-error-key="correctAnswer">
                                        <option value="">-- Select --</option>
                                        <option value="True" {{ old('correctAnswer') == 'True' ? 'selected' : '' }}>True</option>
                                        <option value="False" {{ old('correctAnswer') == 'False' ? 'selected' : '' }}>False</option>
                                    </select>
                                </div>
                            </div>

                            <p id="matchingHint" class="work-note work-dyn" style="display:none;">
                                No choices needed here — at print time, the system automatically gathers the answers from all your other Matching Type questions in this exam and shuffles them into an answer pool.
                            </p>
                            <p id="essayHint" class="work-note work-dyn" style="display:none;">
                                Optional: use this for your own grading rubric/reference. It will not appear on the printed exam.
                            </p>
                        </div>
                    </section>

                    <div class="work-form-actions">
                        <button type="submit" class="work-action-primary" data-pending-label="Saving…">Save question <span aria-hidden="true">→</span></button>
                        <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}" class="work-form-cancel">Cancel</a>
                    </div>
                </form>

                <aside class="work-aside" aria-label="Question types">
                    <h2>Answer format by type</h2>
                    <ol>
                        <li><span class="work-step-num">MC</span><div><strong>Multiple Choice</strong><p>Add each choice with the Add answer button, then enter the correct answer.</p></div></li>
                        <li><span class="work-step-num">T/F</span><div><strong>True/False</strong><p>Pick True or False as the answer.</p></div></li>
                        <li><span class="work-step-num">Enum</span><div><strong>Enumeration</strong><p>Separate the correct answers with commas.</p></div></li>
                        <li><span class="work-step-num">Match</span><div><strong>Matching Type</strong><p>Enter the answer for this item. The answer pool is built at print time.</p></div></li>
                        <li><span class="work-step-num">Essay</span><div><strong>Essay</strong><p>The answer field is an optional rubric and is never printed.</p></div></li>
                    </ol>
                </aside>
            </div>
        </main>
        <footer class="work-footer"><span>DESBank</span><span>Assessment authoring workspace</span></footer>
    </div>

    <script src="{{ asset('js/question-choices.js') }}?v={{ @filemtime(public_path('js/question-choices.js')) }}"></script>
    <script src="{{ asset('js/question-blanks.js') }}?v={{ @filemtime(public_path('js/question-blanks.js')) }}"></script>
    <script>
        const form = document.getElementById('questionForm');
        const typeSelect = document.getElementById('questionType');
        const choicesField = document.getElementById('choicesField');
        const answerTextField = document.getElementById('answerTextField');
        const answerTrueFalseField = document.getElementById('answerTrueFalseField');
        const answerLabel = document.getElementById('answerLabel');
        const answerInput = document.getElementById('answerInput');
        const answerTrueFalseSelect = document.getElementById('answerTrueFalseSelect');
        const matchingHint = document.getElementById('matchingHint');
        const essayHint = document.getElementById('essayHint');

        function updateFields() {
            const type = typeSelect.value;

            choicesField.style.display = 'none';
            answerTextField.style.display = 'block';
            answerTrueFalseField.style.display = 'none';
            matchingHint.style.display = 'none';
            essayHint.style.display = 'none';
            answerLabel.textContent = 'Correct Answer';
            answerInput.required = true;

            if (type === 'Multiple Choice') {
                choicesField.style.display = 'block';
            } else if (type === 'True/False') {
                answerTextField.style.display = 'none';
                answerTrueFalseField.style.display = 'block';
                answerInput.required = false;
            } else if (type === 'Enumeration') {
                answerLabel.textContent = 'Correct Answers (separate with commas)';
            } else if (type === 'Matching Type') {
                answerLabel.textContent = 'Correct Answer for This Item';
                matchingHint.style.display = 'block';
            } else if (type === 'Essay') {
                answerLabel.textContent = 'Suggested Answer / Rubric (optional)';
                answerInput.required = false;
                essayHint.style.display = 'block';
            }
        }

        // Only animate the fields once the person has changed the type themselves.
        typeSelect.addEventListener('change', function () {
            form.classList.add('is-interacted');
            updateFields();
        });
        updateFields();

        answerTrueFalseSelect.addEventListener('change', function () {
            answerInput.value = this.value;
        });

        form.addEventListener('submit', function () {
            if (typeSelect.value === 'True/False') {
                answerInput.value = answerTrueFalseSelect.value;
            }
        });
    </script>
</body>
</html>
