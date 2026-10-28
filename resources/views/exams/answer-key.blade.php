<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Answer Key - DESBank</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f5f7; margin: 0; padding: 20px; }
        .layout { display: flex; gap: 20px; max-width: 1100px; margin: 0 auto; align-items: flex-start; }
        .controls { width: 220px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); flex-shrink: 0; }
        .controls h2 { font-size: 15px; margin-top: 0; color: #1f2937; }
        .controls select { width: 100%; padding: 6px; margin-top: 6px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 13px; }
        .controls button { margin-top: 14px; width: 100%; padding: 8px; background: #16a34a; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .back-link { display: block; margin-top: 14px; color: #2563eb; text-decoration: none; font-size: 13px; }

        .preview-wrapper { flex: 1; display: flex; justify-content: center; background: #d1d5db; padding: 20px; border-radius: 8px; }
        .paper { background: #fff; padding: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.2); width: 8.5in; min-height: 11in; }
        .paper.legal { min-height: 14in; }

        .ak-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .ak-header .school { font-size: 15px; font-weight: bold; }
        .ak-header .title { font-size: 18px; font-weight: bold; margin-top: 8px; letter-spacing: 1px; }
        .ak-header .sub { font-size: 13px; margin-top: 4px; color: #374151; }

        .meta-row { display: flex; justify-content: space-between; font-size: 12px; color: #374151; margin-bottom: 20px; }

        .section-title { font-weight: bold; font-size: 13px; margin-top: 18px; margin-bottom: 6px; text-transform: uppercase; border-bottom: 1px solid #d1d5db; padding-bottom: 3px; }
        .answer-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px 12px; }
        .answer-cell { font-size: 13px; }
        .answer-cell b { display: inline-block; min-width: 22px; }

        @media print {
            body { background: #fff; padding: 0; }
            .controls, .back-link { display: none; }
            .layout { display: block; }
            .preview-wrapper { background: none; padding: 0; }
            .paper { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <div class="controls">
            <h2>Answer Key Options</h2>
            <label style="font-size:13px;">Paper Size</label>
            <select id="paperSizeSelect" onchange="document.getElementById('answerPaper').className = 'paper ' + this.value;">
                <option value="letter">Letter (Short)</option>
                <option value="legal">Legal (Long)</option>
            </select>

            <button type="button" onclick="window.print()">Print / Save as PDF</button>

            <a href="{{ route('courses.exams.show', [$course, $exam]) }}" class="back-link">&larr; Back to Exam</a>
        </div>

        <div class="preview-wrapper">
            <div class="paper letter" id="answerPaper">
                <div class="ak-header">
                    <div class="school">{{ $exam->schoolName ?? $course->courseName }}</div>
                    <div class="title">ANSWER KEY</div>
                    <div class="sub">{{ $tos->title ?? 'Exam' }} — {{ $tos->examType ?? '' }}</div>
                </div>

                <div class="meta-row">
                    <span><strong>Instructor:</strong> {{ auth()->user()->name }}</span>
                    <span><strong>Date Created:</strong> {{ \Carbon\Carbon::parse($exam->created)->format('F j, Y') }}</span>
                    <span><strong>Total Items:</strong> {{ $exam->totalItems }}</span>
                </div>

                @foreach ($sections as $type => $items)
                    <div class="section-title">{{ $type }}</div>
                    <div class="answer-grid">
                        @foreach ($items as $item)
                            <div class="answer-cell"><b>{{ $item['number'] }}.</b> {{ $item['answer'] }}</div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</body>
</html>