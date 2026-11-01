<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Question · DESBank</title>
    @include('partials.head-assets')
    @include('partials.workspace-assets')
</head>
<body class="workspace">
    <div class="db-shell">
        @include('partials.navbar')

        <main id="main-content" class="work-main">
            <nav class="work-crumb" aria-label="Breadcrumb">
                <a href="{{ route('courses.index') }}">Courses</a><span aria-hidden="true">/</span><a href="{{ route('courses.topics.index', $course) }}">{{ $course->courseName }}</a><span aria-hidden="true">/</span><a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}">{{ $topic->topicName }}</a><span aria-hidden="true">/</span><span aria-current="page">Edit question</span>
            </nav>
            <header class="work-heading work-heading-compact">
                <div>
                    <p class="work-eyebrow"><span class="work-rule" aria-hidden="true"></span> Question bank</p>
                    <h1>Edit question<span class="work-title-period">.</span></h1>
                    <p class="work-intro">Update an item in the question bank for <strong>{{ $topic->topicName }}</strong> in {{ $course->courseName }}.</p>
                </div>
            </header>

            <div class="work-form-layout">
                <form method="POST" action="{{ route('courses.topics.questions.update', [$course, $topic, $question]) }}" class="work-form" id="questionForm" data-pending>
                    @csrf
                    @method('PUT')
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
                                        @foreach ($bloomLevels as $level)
                                            <option value="{{ $level }}" {{ old('bloomLevel', $question->bloomLevel) == $level ? 'selected' : '' }}>{{ $level }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="work-field">
                                    <label class="work-label" for="questionType">Question type</label>
                                    <select name="questionType" id="questionType" required class="work-select {{ $errors->has('questionType') ? 'is-invalid' : '' }}">
                                        @foreach ($questionTypes as $type)
                                            <option value="{{ $type }}" {{ old('questionType', $question->questionType) == $type ? 'selected' : '' }}>{{ $type }}</option>
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
                                <textarea name="content" id="content" rows="4" required class="work-textarea {{ $errors->has('content') ? 'is-invalid' : '' }}">{{ old('content', $question->content) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <section class="work-section" aria-labelledby="qf-answer">
                        <div class="work-section-head">
                            <span class="work-section-num" aria-hidden="true">03</span>
                            <div><h2 id="qf-answer">Answer</h2><p>The fields below change with the question type.</p></div>
                        </div>
                        <div class="work-section-body">
                            <div id="choicesField" class="work-dyn">
                                <div class="work-field">
                                    <label class="work-label" for="choicesInput">Choices <span class="work-label-opt">(separate with commas)</span></label>
                                    <input type="text" name="choices" id="choicesInput" class="work-input" value="{{ old('choices', $question->choices) }}">
                                </div>
                            </div>

                            <div id="answerTextField" class="work-dyn">
                                <div class="work-field">
                                    <label class="work-label" id="answerLabel" for="answerInput">Correct Answer</label>
                                    <input type="text" name="correctAnswer" id="answerInput" class="work-input" value="{{ old('correctAnswer', $question->correctAnswer) }}">
                                </div>
                            </div>

                            <div id="answerTrueFalseField" class="work-dyn" style="display:none;">
                                <div class="work-field">
                                    <label class="work-label" for="answerTrueFalseSelect">Correct Answer</label>
                                    <select id="answerTrueFalseSelect" class="work-select">
                                        <option value="">-- Select --</option>
                                        <option value="True" {{ $question->correctAnswer == 'True' ? 'selected' : '' }}>True</option>
                                        <option value="False" {{ $question->correctAnswer == 'False' ? 'selected' : '' }}>False</option>
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
                        <button type="submit" class="work-action-primary" data-pending-label="Updating…">Update question <span aria-hidden="true">→</span></button>
                        <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}" class="work-form-cancel">Cancel</a>
                    </div>
                </form>

                <aside class="work-aside" aria-label="Question types">
                    <h2>Answer format by type</h2>
                    <ol>
                        <li><span class="work-step-num">MC</span><div><strong>Multiple Choice</strong><p>List the choices separated by commas, then enter the correct answer.</p></div></li>
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
            document.getElementById('choicesInput').required = false;

            if (type === 'Multiple Choice') {
                choicesField.style.display = 'block';
                document.getElementById('choicesInput').required = true;
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
