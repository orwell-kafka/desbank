<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Table of Specification - {{ \App\Models\Tos::headingTitle($course) }}</title>
    <style>
        @page { size: Letter; margin: 0.6in; }
        * { box-sizing: border-box; }
        body { font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 12pt; color: #000; background: #e5e7eb; margin: 0; padding: 20px; }
        .toolbar { max-width: 8.5in; margin: 0 auto 14px; display: flex; gap: 10px; align-items: center; font-family: Arial, sans-serif; font-size: 13px; }
        .toolbar button { background: #16a34a; color: #fff; border: 0; border-radius: 4px; padding: 8px 18px; font-size: 13px; cursor: pointer; }
        .toolbar a { color: #2563eb; text-decoration: none; }
        .paper { width: 8.5in; max-width: 100%; min-height: 11in; margin: 0 auto; background: #fff; padding: 0.6in; box-shadow: 0 1px 6px rgba(0,0,0,.2); }
        .paper .tos-heading { border: 0; padding: 0; margin: 0 0 22px; font-size: 12pt; }
        h1 { font-size: 12pt; text-align: center; font-weight: bold; text-transform: uppercase; margin: 0 0 14px; }
        table.spec { width: 100%; border-collapse: collapse; margin-bottom: 26px; font-size: 11pt; }
        table.spec th, table.spec td { border: 1px solid #000; padding: 4px 8px; vertical-align: middle; }
        table.spec thead th { text-align: center; font-weight: bold; padding: 7px 8px; }
        table.spec td.c { text-align: center; }
        table.spec td.topic { text-align: left; }
        table.spec tfoot td { font-weight: bold; }
        table.spec tfoot td.lbl { text-align: right; }
        table.spec tr { break-inside: avoid; }
        table.levels td.lvl { text-align: left; }
        @media print {
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .paper { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @php
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
        $totalHours = $topicRows->sum('contactHours');
        $totalPoints = $topicRows->sum('assignedPoints');
    @endphp
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
        <a href="{{ route('courses.tos.show', [$course, $tos]) }}">← Back to ToS</a>
    </div>

    <div class="paper">
        @include('tos._heading')

        <h1>Table of Specification</h1>

        <table class="spec">
            <thead><tr>
                <th>Topics</th>
                <th>Contact Hours</th>
                <th>Percentage per Topic</th>
                <th>Assigned Point per Topic</th>
            </tr></thead>
            <tbody>
                @foreach ($topicRows as $row)
                    <tr>
                        <td class="topic">{{ $loop->iteration }}. {{ $row->topicName }}</td>
                        <td class="c">{{ $fmt($row->contactHours) }}</td>
                        <td class="c">{{ $topicPercent[$row->topicId] ?? 0 }}%</td>
                        <td class="c">{{ $fmt($row->assignedPoints) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot><tr>
                <td class="lbl">Total:</td>
                <td class="c">{{ $fmt($totalHours) }}</td>
                <td class="c">100%</td>
                <td class="c">{{ $fmt($totalPoints) }}</td>
            </tr></tfoot>
        </table>

        <table class="spec levels">
            <thead><tr>
                <th>Level of Learning</th>
                <th>Type of Test</th>
                <th>Item Numbers</th>
                <th>No. of Items</th>
                <th>Percentage</th>
            </tr></thead>
            <tbody>
                @foreach ($levels as $name => $level)
                    @php $n = count($level['rows']); @endphp
                    @if ($n === 0)
                        <tr><td class="lvl">{{ $name }}</td><td></td><td></td><td></td><td></td></tr>
                    @else
                        @foreach ($level['rows'] as $i => $r)
                            <tr>
                                @if ($i === 0)<td class="lvl" rowspan="{{ $n }}">{{ $name }}</td>@endif
                                <td class="c">{{ $r['type'] }}</td>
                                <td class="c">{{ $r['numbers'] }}</td>
                                <td class="c">{{ $r['count'] }}</td>
                                @if ($i === 0)<td class="c" rowspan="{{ $n }}">{{ $level['percent'] }}%</td>@endif
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
            <tfoot><tr>
                <td class="lbl" style="text-align:left;">Total</td>
                <td></td>
                <td></td>
                <td class="c">{{ $totalItems }}</td>
                <td class="c">100%</td>
            </tr></tfoot>
        </table>
    </div>
</body>
</html>
