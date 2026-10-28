<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create ToS - {{ $course->courseName }} - DESBank</title>
    @include('partials.head-assets')
    <style>
        h3 { font-family: var(--font-display); font-size: 15px; color: var(--navy); margin-top: 28px; border-bottom: 2px solid var(--border); padding-bottom: 6px; }
        .hint { color: var(--text-muted); font-size: 12px; margin-top: 4px; }
        .hours-input, .count-input { width: 80px; padding: 5px; border: 1px solid var(--border); border-radius: 4px; font-family: var(--font-body); }
    </style>
</head>
<body>
    <div class="db-shell" style="max-width:900px;">
        @include('partials.navbar')
        <div class="db-card">
            <h1>Create ToS</h1>
            <p class="db-subtitle">{{ $course->courseName }} ({{ $course->courseCode }})</p>

            @if ($errors->any())
                <div class="db-alert db-alert-error">{{ $errors->first() }}</div>
            @endif

            @if ($topics->isEmpty())
                <p class="db-alert db-alert-error">This course has no topics yet. Add topics before creating a ToS.</p>
            @else
                <form method="POST" action="{{ route('courses.tos.store', $course) }}">
                    @csrf

                    <label class="db-label">ToS Title</label>
                    <input type="text" name="title" class="db-input" value="{{ old('title') }}" required>

                    <label class="db-label">Exam Type</label>
                    <select name="examType" class="db-select" required>
                        <option value="">-- Select --</option>
                        @foreach ($examTypes as $type)
                            <option value="{{ $type }}" {{ old('examType') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>

                    <label class="db-label">Total Score</label>
                    <input type="number" step="0.01" name="totalScore" class="db-input" value="{{ old('totalScore') }}" required>

                    <h3>Table 1 — Topics &amp; Contact Hours</h3>
                    <p class="hint">Check each topic covered by this exam and enter its contact hours.</p>
                    <table class="db-table">
                        <tr><th>Include</th><th>Topic</th><th>Contact Hours</th></tr>
                        @foreach ($topics as $topic)
                            <tr>
                                <td><input type="checkbox" name="topicId[]" value="{{ $topic->topicId }}"></td>
                                <td>{{ $topic->topicName }}</td>
                                <td><input type="number" step="0.5" min="0" class="hours-input" name="contactHours[{{ $topic->topicId }}]" value="0"></td>
                            </tr>
                        @endforeach
                    </table>

                    <h3>Table 2 — Bloom's Taxonomy Distribution</h3>
                    <p class="hint">Pick a Bloom level, question type, points per item, and item count, then click Add. The total score across all added rows must equal the Total Score above.</p>

                    <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap; background:var(--canvas); border:1px solid var(--border); padding:12px; border-radius:6px;">
                        <div>
                            <label class="db-label" style="margin-top:0;">Bloom Level</label>
                            <select id="newBloomLevel" class="db-select">
                                @foreach ($bloomLevels as $level)
                                    <option value="{{ $level }}">{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="db-label" style="margin-top:0;">Question Type</label>
                            <select id="newQuestionType" class="db-select">
                                @foreach ($questionTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="db-label" style="margin-top:0;">Points/Item</label>
                            <input type="number" step="0.5" min="0" id="newPoints" value="1" class="db-input" style="width:90px;">
                        </div>
                        <div>
                            <label class="db-label" style="margin-top:0;">Item Count</label>
                            <input type="number" min="0" id="newCount" value="1" class="db-input" style="width:90px;">
                        </div>
                        <div>
                            <button type="button" id="addBloomRowBtn" class="db-btn db-btn-primary" style="margin-top:0;">+ Add</button>
                        </div>
                    </div>

                    <table class="db-table" id="bloomRowsTable">
                        <tr><th>Bloom Level</th><th>Question Type</th><th>Points/Item</th><th>Item Count</th><th>Score</th><th></th></tr>
                    </table>
                    <div id="bloomRunningTotal" style="margin-top:8px; font-size:13px; color:var(--text-muted); font-family:var(--font-mono);"></div>
                    <div id="bloomHiddenInputs"></div>

                    <button type="submit" class="db-btn db-btn-primary" style="margin-top:25px;">Save ToS</button>

                    <script>
                        (function () {
                            const rows = [];
                            const tableEl = document.getElementById('bloomRowsTable');
                            const totalEl = document.getElementById('bloomRunningTotal');
                            const form = tableEl.closest('form');

                            function render() {
                                tableEl.innerHTML = '<tr><th>Bloom Level</th><th>Question Type</th><th>Points/Item</th><th>Item Count</th><th>Score</th><th></th></tr>';
                                let total = 0;
                                rows.forEach(function (row, i) {
                                    const score = row.points * row.count;
                                    total += score;
                                    const tr = document.createElement('tr');
                                    tr.innerHTML = '<td>' + row.level + '</td><td>' + row.type + '</td><td>' + row.points + '</td><td>' + row.count + '</td><td>' + score.toFixed(2) + '</td><td><button type="button" data-i="' + i + '" class="removeBloomRow db-btn db-btn-danger" style="padding:3px 8px;">Remove</button></td>';
                                    tableEl.appendChild(tr);
                                });
                                totalEl.textContent = 'Running total from Table 2: ' + total.toFixed(2);

                                tableEl.querySelectorAll('.removeBloomRow').forEach(function (btn) {
                                    btn.addEventListener('click', function () {
                                        rows.splice(parseInt(this.dataset.i), 1);
                                        render();
                                    });
                                });
                            }

                            document.getElementById('addBloomRowBtn').addEventListener('click', function () {
                                const level = document.getElementById('newBloomLevel').value;
                                const type = document.getElementById('newQuestionType').value;
                                const points = parseFloat(document.getElementById('newPoints').value) || 0;
                                const count = parseInt(document.getElementById('newCount').value) || 0;

                                if (points <= 0 || count <= 0) {
                                    alert('Points per item and item count must both be greater than zero.');
                                    return;
                                }

                                const existingIndex = rows.findIndex(r => r.level === level && r.type === type);
                                if (existingIndex >= 0) {
                                    rows[existingIndex] = { level, type, points, count };
                                } else {
                                    rows.push({ level, type, points, count });
                                }
                                render();
                            });

                            form.addEventListener('submit', function () {
                                const container = document.getElementById('bloomHiddenInputs');
                                container.innerHTML = '';
                                rows.forEach(function (row) {
                                    const key = row.level + '|' + row.type;
                                    const keyInput = document.createElement('input');
                                    keyInput.type = 'hidden';
                                    keyInput.name = 'bloomKey[]';
                                    keyInput.value = key;
                                    container.appendChild(keyInput);

                                    const pointsInput = document.createElement('input');
                                    pointsInput.type = 'hidden';
                                    pointsInput.name = 'pointsPerItem[' + key + ']';
                                    pointsInput.value = row.points;
                                    container.appendChild(pointsInput);

                                    const countInput = document.createElement('input');
                                    countInput.type = 'hidden';
                                    countInput.name = 'itemCount[' + key + ']';
                                    countInput.value = row.count;
                                    container.appendChild(countInput);
                                });
                            });

                            render();
                        })();
                    </script>
                </form>
            @endif

            <a href="{{ route('courses.tos.index', $course) }}" class="db-link db-back-link">&larr; Back to ToS List</a>
        </div>
    </div>
</body>
</html>