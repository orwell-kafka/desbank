<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Question - DESBank</title>
    @include('partials.head-assets')
</head>
<body>
    <div class="db-shell" style="max-width:600px;">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Add Question</h1>
            <p class="db-subtitle">{{ $course->courseName }} &rarr; {{ $topic->topicName }}</p>

            @if ($errors->any())
                <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('courses.topics.questions.store', [$course, $topic]) }}">
                @csrf

                <label class="db-label">Bloom's Taxonomy Level</label>
                <select name="bloomLevel" required class="db-select">
                    <option value="">-- Select --</option>
                    @foreach ($bloomLevels as $level)
                        <option value="{{ $level }}" {{ old('bloomLevel') == $level ? 'selected' : '' }}>{{ $level }}</option>
                    @endforeach
                </select>

                <label class="db-label">Question Type</label>
                <select name="questionType" id="questionType" required class="db-select">
                    <option value="">-- Select --</option>
                    @foreach ($questionTypes as $type)
                        <option value="{{ $type }}" {{ old('questionType') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>

                <label class="db-label">Question</label>
                <textarea name="content" rows="3" required class="db-textarea">{{ old('content') }}</textarea>

                <div id="choicesField">
                    <label class="db-label">Choices (separate with commas)</label>
                    <input type="text" name="choices" id="choicesInput" class="db-input" value="{{ old('choices') }}">
                </div>

                <div id="answerTextField">
                    <label class="db-label" id="answerLabel">Correct Answer</label>
                    <input type="text" name="correctAnswer" id="answerInput" class="db-input" value="{{ old('correctAnswer') }}">
                </div>

                <div id="answerTrueFalseField" style="display:none;">
                    <label class="db-label">Correct Answer</label>
                    <select id="answerTrueFalseSelect" class="db-select">
                        <option value="">-- Select --</option>
                        <option value="True">True</option>
                        <option value="False">False</option>
                    </select>
                </div>

                <p id="matchingHint" style="display:none; color:var(--text-muted); font-size:12px; margin-top:8px;">
                    No choices needed here — at print time, the system automatically gathers the answers from all your other Matching Type questions in this exam and shuffles them into an answer pool.
                </p>
                <p id="essayHint" style="display:none; color:var(--text-muted); font-size:12px; margin-top:8px;">
                    Optional: use this for your own grading rubric/reference. It will not appear on the printed exam.
                </p>

                <button type="submit" class="db-btn db-btn-primary" style="margin-top:20px;">Save Question</button>
            </form>

            <a href="{{ route('courses.topics.questions.index', [$course, $topic]) }}" class="db-link db-back-link">&larr; Back to Questions</a>
        </div>
    </div>

    <script>
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

        typeSelect.addEventListener('change', updateFields);
        updateFields();

        answerTrueFalseSelect.addEventListener('change', function () {
            answerInput.value = this.value;
        });

        document.querySelector('form').addEventListener('submit', function () {
            if (typeSelect.value === 'True/False') {
                answerInput.value = answerTrueFalseSelect.value;
            }
        });
    </script>
</body>
</html>