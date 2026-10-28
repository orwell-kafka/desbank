<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Format Exam - DESBank</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f5f7; margin: 0; padding: 20px; }
        .layout { display: flex; gap: 20px; max-width: 1200px; margin: 0 auto; align-items: flex-start; }
        .settings { width: 320px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); flex-shrink: 0; }
        .settings h2 { font-size: 16px; color: #1f2937; margin-top: 0; }
        .settings h3 { font-size: 13px; color: #374151; margin-top: 18px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
        .settings label { display: block; margin-top: 12px; font-size: 13px; color: #374151; }
        .settings input, .settings select { width: 100%; padding: 6px; margin-top: 4px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px; }
        .settings input[type=file] { padding: 4px 0; }
        .settings .checkbox-line { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 12px; }
        .settings .checkbox-line input { width: auto; margin: 0; }
        .settings button { margin-top: 16px; width: 100%; padding: 8px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .settings .print-btn { background: #16a34a; margin-top: 8px; }
        .status { color: #15803d; font-size: 12px; margin-bottom: 8px; }
        .error { color: #b91c1c; font-size: 12px; margin-bottom: 8px; }
        .back-link { display: block; margin-top: 14px; color: #2563eb; text-decoration: none; font-size: 13px; }
        .current-img { max-width: 100px; max-height: 60px; display: block; margin-top: 6px; border: 1px solid #e5e7eb; }
        .hint { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        .preview-wrapper { flex: 1; display: flex; justify-content: center; background: #d1d5db; padding: 20px; border-radius: 8px; }
        .paper { background: #fff; padding: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); position: relative; width: 8.5in; min-height: 11in; overflow: hidden; }
        .paper.legal { width: 8.5in; min-height: 14in; }

        .watermark-text { position: absolute; top: 45%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 60px; color: rgba(0,0,0,0.06); font-weight: bold; white-space: nowrap; pointer-events: none; z-index: 0; }
        .watermark-img { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); max-width: 60%; max-height: 60%; opacity: 0.08; pointer-events: none; z-index: 0; }
        .paper > * { position: relative; z-index: 1; }

        /* ---- Header templates (developer-designed, fixed) ---- */
        .header-classic { text-align: center; padding: 0 0 12px 0; margin-bottom: 15px; }
        .header-classic img { width: 100%; display: block; margin-bottom: 8px; }
        .header-classic .school { font-size: 18px; font-weight: bold; letter-spacing: 0.5px; }
        .header-classic .dept { font-size: 13px; margin-top: 2px; }
        .header-classic .title { font-size: 14px; font-weight: bold; margin-top: 10px; text-transform: uppercase; }

        .header-formal { padding: 20px 0; margin-bottom: 15px; text-align: center; }
        .header-formal img { width: 100%; display: block; margin-bottom: 8px; }
        .header-formal .school { font-size: 17px; font-weight: bold; }
        .header-formal .dept { font-size: 13px; margin-top: 2px; }
        .header-formal .title { font-size: 14px; font-weight: bold; margin-top: 10px; }

        .header-minimal { text-align: left; padding-bottom: 8px; margin-bottom: 15px; }
        .header-minimal img { width: 100%; display: block; margin-bottom: 6px; }
        .header-minimal .school { font-size: 14px; font-weight: bold; }
        .header-minimal .dept { font-size: 12px; color: #4b5563; }
        .header-minimal .title { font-size: 13px; font-weight: bold; margin-top: 6px; }

        .info-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 15px; }
        .info-row span { border-bottom: 1px solid #000; min-width: 150px; display: inline-block; }

        .part-header { font-weight: bold; font-size: 13px; margin-top: 20px; margin-bottom: 8px; text-transform: uppercase; }
        .part-direction { font-size: 12px; font-style: italic; margin-bottom: 10px; }
        .question-list { columns: var(--cols, 1); column-gap: 30px; }
        .question-item { font-size: 13px; margin-bottom: 10px; break-inside: avoid; }
        .choices { margin-top: 4px; margin-left: 15px; }
        .choices div { font-size: 12px; margin-bottom: 2px; }
        .essay-lines { margin-top: 8px; height: 100px; }

        .matching-layout { display: flex; gap: 30px; margin-top: 8px; }
        .matching-col-a { flex: 1; }
        .matching-col-a .item { font-size: 13px; margin-bottom: 10px; }
        .matching-col-b { flex-shrink: 0; min-width: 160px; padding-left: 20px; }        .matching-col-b .item { font-size: 13px; margin-bottom: 8px; }
        /* ---- Footer templates (developer-designed, fixed) ---- */
        .footer-simple { text-align: center; font-size: 11px; color: #6b7280; margin-top: 30px; border-top: 1px solid #d1d5db; padding-top: 8px; }

        .footer-paged { display: flex; justify-content: space-between; font-size: 11px; color: #6b7280; margin-top: 30px; border-top: 2px solid #000; padding-top: 8px; }

        .footer-minimal { text-align: center; font-size: 10px; color: #9ca3af; margin-top: 25px; }

        @media print {
            body { background: #fff; padding: 0; }
            .settings, .back-link { display: none; }
            .layout { display: block; }
            .preview-wrapper { background: none; padding: 0; }
            .paper { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>
    @php
    $headerImageMap = [
        'classic' => 'images/exam-templates/header-1.png',
        'formal' => 'images/exam-templates/header-2.png',
        'minimal' => 'images/exam-templates/header-minimal.svg',
    ];
    $footerImageMap = [
        'simple' => 'images/exam-templates/footer-simple.svg',
        'paged' => 'images/exam-templates/footer-paged.svg',
        'minimal' => 'images/exam-templates/footer-minimal.svg',
    ];
@endphp

    <div class="layout">
        <div class="settings">
            <h2>Format Settings</h2>

            @if (session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('courses.exams.format.update', [$course, $exam]) }}" enctype="multipart/form-data">
                @csrf

                <label>Paper Size</label>
                <select name="paperSize">
                    <option value="Letter" {{ $exam->paperSize == 'Letter' ? 'selected' : '' }}>Letter (Short)</option>
                    <option value="Legal" {{ $exam->paperSize == 'Legal' ? 'selected' : '' }}>Legal (Long)</option>
                </select>

                <label>School Name</label>
                <input type="text" name="schoolName" value="{{ $exam->schoolName }}">

                <label>Department</label>
                <input type="text" name="department" value="{{ $exam->department }}">

                <label>Columns per Section</label>
                @foreach ($presentTypes as $type)
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                        <span style="font-size:12px; color:#4b5563;">{{ $type }}</span>
                        <select name="columns[{{ $type }}]" style="width:70px;">
                            <option value="1" {{ ($columnsPerPart[$type] ?? 1) == 1 ? 'selected' : '' }}>1</option>
                            <option value="2" {{ ($columnsPerPart[$type] ?? 1) == 2 ? 'selected' : '' }}>2</option>
                            <option value="3" {{ ($columnsPerPart[$type] ?? 1) == 3 ? 'selected' : '' }}>3</option>
                        </select>
                    </div>
                @endforeach

                <h3>Section Order</h3>
                <div class="hint" style="margin-bottom:6px;">Drag to reorder which section prints first.</div>
                @php $currentOrder = $questions->keys(); @endphp
                <ul id="sectionOrderList" style="list-style:none; padding:0; margin:0;">
                    @foreach ($currentOrder as $type)
                        <li draggable="true" data-type="{{ $type }}" style="background:#f9fafb; border:1px solid #d1d5db; border-radius:4px; padding:8px 10px; margin-bottom:6px; font-size:13px; cursor:grab; user-select:none;">
                            &#9776; {{ $type }}
                        </li>
                    @endforeach
                </ul>
                <div id="partOrderInputs"></div>

                <h3>Header Template</h3>
                <select name="headerTemplate">
                    <option value="classic" {{ $exam->headerTemplate == 'classic' ? 'selected' : '' }}>Classic (double border, centered)</option>
                    <option value="formal" {{ $exam->headerTemplate == 'formal' ? 'selected' : '' }}>Formal (boxed border)</option>
                    <option value="minimal" {{ $exam->headerTemplate == 'minimal' ? 'selected' : '' }}>Minimal (left-aligned)</option>
                </select>
                <div class="hint">Pre-designed layout — school name/department text only, no custom logo.</div>

                <h3>Footer Text</h3>
                <input type="text" name="footerText" value="{{ $exam->footerText }}">

                <h3>Footer Template</h3>
                <select name="footerTemplate">
                    <option value="simple" {{ $exam->footerTemplate == 'simple' ? 'selected' : '' }}>Simple (centered, thin line)</option>
                    <option value="paged" {{ $exam->footerTemplate == 'paged' ? 'selected' : '' }}>Paged (left/right split, bold line)</option>
                    <option value="minimal" {{ $exam->footerTemplate == 'minimal' ? 'selected' : '' }}>Minimal (small, no line)</option>
                </select>

                <h3>Watermark Text</h3>
                <input type="text" name="watermarkText" value="{{ $exam->watermarkText }}">

                <h3>Watermark Image (overrides text if set)</h3>
                @if ($exam->watermarkImagePath)
                    <img src="{{ asset('storage/' . $exam->watermarkImagePath) }}" class="current-img">
                    <div class="checkbox-line">
                        <input type="checkbox" name="removeWatermarkImage" value="1" id="rmWatermark">
                        <label for="rmWatermark" style="margin:0;">Remove current image</label>
                    </div>
                @endif
                <input type="file" name="watermarkImage" accept="image/*">

                <button type="submit">Update Preview</button>
            
<script>
                    (function () {
                        const list = document.getElementById('sectionOrderList');
                        const form = list.closest('form');
                        let dragging = null;

                        list.addEventListener('dragstart', function (e) {
                            dragging = e.target;
                            e.target.style.opacity = '0.4';
                        });

                        list.addEventListener('dragend', function (e) {
                            e.target.style.opacity = '1';
                        });

                        list.addEventListener('dragover', function (e) {
                            e.preventDefault();
                            const afterElement = getDragAfterElement(list, e.clientY);
                            if (afterElement == null) {
                                list.appendChild(dragging);
                            } else {
                                list.insertBefore(dragging, afterElement);
                            }
                        });

                        function getDragAfterElement(container, y) {
                            const items = [...container.querySelectorAll('li:not([style*="opacity: 0.4"])')];
                            return items.reduce((closest, child) => {
                                const box = child.getBoundingClientRect();
                                const offset = y - box.top - box.height / 2;
                                if (offset < 0 && offset > closest.offset) {
                                    return { offset: offset, element: child };
                                } else {
                                    return closest;
                                }
                            }, { offset: Number.NEGATIVE_INFINITY }).element;
                        }

                        form.addEventListener('submit', function () {
                            const container = document.getElementById('partOrderInputs');
                            container.innerHTML = '';
                            list.querySelectorAll('li').forEach(function (li) {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'partOrder[]';
                                input.value = li.dataset.type;
                                container.appendChild(input);
                            });
                        });
                    })();
                </script>
            </form>

            <button type="button" class="print-btn" onclick="window.print()">Print / Save as PDF</button>

            <a href="{{ route('courses.exams.show', [$course, $exam]) }}" class="back-link">&larr; Back to Exam</a>
        </div>

        <div class="preview-wrapper">
            <div class="paper {{ strtolower($exam->paperSize ?? 'letter') }}">
                @if ($exam->watermarkImagePath)
                    <img src="{{ asset('storage/' . $exam->watermarkImagePath) }}" class="watermark-img">
                @elseif ($exam->watermarkText)
                    <div class="watermark-text">{{ $exam->watermarkText }}</div>
                @endif

                @php $headerClass = 'header-' . ($exam->headerTemplate ?? 'classic'); @endphp
                <div class="{{ $headerClass }}">
                    <img src="{{ asset($headerImageMap[$exam->headerTemplate ?? 'classic']) }}">
                    <div class="school">{{ $exam->schoolName ?? 'School Name' }}</div>
                    <div class="dept">{{ $exam->department ?? 'Department' }}</div>
                    <div class="title">{{ $tos->title ?? 'Exam' }} — {{ $tos->examType ?? '' }}</div>
                </div>

                <div class="info-row">
                    <span>Name:</span>
                    <span>Score: ___ / {{ $exam->totalScore }}</span>
                    <span>Date:</span>
                    <span>Section:</span>
                </div>

               @php $partNum = 1; $romanParts = ['I','II','III','IV','V','VI']; $letters = ['A','B','C','D','E','F','G','H','I','J','K','L']; @endphp
                @foreach ($questions as $type => $group)
                    @php $itemCounter = 1; @endphp
                    <div class="part-header">
                        PART {{ $romanParts[$partNum - 1] ?? $partNum }} — {{ strtoupper($type) }}
                        (ITEMS 1–{{ $group->count() }})
                    </div>
                    <div class="part-direction">Direction: {{ $directions[$type] ?? '' }}</div>

                   @if ($type === 'Matching Type')
                        @php
                            mt_srand($exam->examId);
                            $shuffledArr = $group->sortBy('questionId')->pluck('correctAnswer')->values()->all();
                            shuffle($shuffledArr);
                            $shuffledAnswers = collect($shuffledArr);
                        @endphp

                        <div class="matching-layout">
                            <div class="matching-col-a">
                                @foreach ($group as $question)
                                    <div class="item">{{ $itemCounter }}. ______ {{ $question->content }}</div>
                                    @php $itemCounter++; @endphp
                                @endforeach
                            </div>
                            <div class="matching-col-b">
                                @foreach ($shuffledAnswers as $i => $answer)
                                    <div class="item">{{ $letters[$i] ?? '' }}. {{ $answer }}</div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        @php $sectionCols = $columnsPerPart[$type] ?? 1; @endphp
                        <div class="question-list" style="--cols: {{ $sectionCols }};">
                            @foreach ($group as $question)
                                <div class="question-item">
                                    {{ $itemCounter }}. {{ $question->content }}
                                    @if ($question->choices)
                                        <div class="choices">
                                            @foreach (explode(',', $question->choices) as $i => $choice)
                                                <div>{{ $letters[$i] ?? '' }}. {{ trim($choice) }}</div>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($type === 'Essay')
                                        <div class="essay-lines"></div>
                                    @endif
                                </div>
                                @php $itemCounter++; @endphp
                            @endforeach
                        </div>
                    @endif

                    @php $partNum++; @endphp
                @endforeach

                @if ($exam->footerText)
                    @php $footerClass = 'footer-' . ($exam->footerTemplate ?? 'simple'); @endphp
                    <img src="{{ asset($footerImageMap[$exam->footerTemplate ?? 'simple']) }}" style="width:100%; display:block; margin-top:30px;">
                    @if ($exam->footerTemplate == 'paged')
                        <div class="{{ $footerClass }}" style="margin-top:4px; border-top:none;">
                            <span>{{ $exam->footerText }}</span>
                            <span>Page 1</span>
                        </div>
                    @else
                        <div class="{{ $footerClass }}" style="margin-top:4px; border-top:none;">{{ $exam->footerText }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</body>
</html>