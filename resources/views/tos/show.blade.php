<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $tos->title }} - DESBank</title>
    @include('partials.head-assets')
    <style>
        h3 { font-family: var(--font-display); font-size: 15px; color: var(--navy); margin-top: 28px; border-bottom: 2px solid var(--border); padding-bottom: 6px; }
        .meta { background: var(--canvas); border: 1px solid var(--border); padding: 12px 16px; border-radius: var(--radius-sm); font-size: 14px; margin-top: 15px; }
        .meta span { display: inline-block; margin-right: 20px; }
        .total-row td { font-weight: bold; border-top: 2px solid var(--navy) !important; }
    </style>
</head>
<body>
    <div class="db-shell" style="max-width:900px;">
        @include('partials.navbar')
        <div class="db-card">
            <h1>{{ $tos->title }}</h1>
            <p class="db-subtitle">{{ $course->courseName }} ({{ $course->courseCode }})</p>

            <div class="meta">
                <span><strong>Exam Type:</strong> {{ $tos->examType }}</span>
                <span><strong>Total Score:</strong> {{ $tos->totalScore }}</span>
            </div>

            <h3>Table 1 — Topics &amp; Contact Hours</h3>
            <table class="db-table">
                <tr><th>Topic</th><th>Contact Hours</th><th>Weight (%)</th><th>Assigned Points</th></tr>
                @php $topicPointsTotal = 0; @endphp
                @foreach ($topicRows as $row)
                    @php $topicPointsTotal += $row->assignedPoints; @endphp
                    <tr>
                        <td>{{ $row->topicName }}</td>
                        <td>{{ $row->contactHours }}</td>
                        <td>{{ $row->generatedWeight }}%</td>
                        <td>{{ $row->assignedPoints }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td>{{ number_format($topicPointsTotal, 2) }}</td>
                </tr>
            </table>

            <h3>Table 2 — Bloom's Taxonomy Distribution</h3>
            <table class="db-table">
                <tr><th>Bloom Level</th><th>Question Type</th><th>Item Count</th><th>Score</th><th>% of Total</th></tr>
                @php $bloomScoreTotal = 0; @endphp
                @foreach ($bloomRows as $row)
                    @php $bloomScoreTotal += $row->generatedScore; @endphp
                    <tr>
                        <td><span class="db-chip">{{ $row->bloomLevel }}</span></td>
                        <td>{{ $row->questionType }}</td>
                        <td>{{ $row->generatedItemCount }}</td>
                        <td>{{ $row->generatedScore }}</td>
                        <td>{{ $tos->totalScore > 0 ? number_format(($row->generatedScore / $tos->totalScore) * 100, 2) : 0 }}%</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3">Total</td>
                    <td>{{ number_format($bloomScoreTotal, 2) }}</td>
                    <td></td>
                </tr>
            </table>

            <a href="{{ route('courses.tos.index', $course) }}" class="db-link db-back-link">&larr; Back to ToS List</a>
        </div>
    </div>
</body>
</html>