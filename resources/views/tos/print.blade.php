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
        .signatories { margin-top: 36px; font-size: 11pt; break-inside: avoid; }
        .signatories .sig-prepared { margin-left: 50%; text-align: center; }
        .signatories .sig-approved { margin-top: 28px; text-align: left; }
        .signatories .sig-label { margin: 0 0 22px; }
        .signatories .sig-approved .sig-label { text-align: left; }
        .signatories .sig-prepared .sig-label { text-align: left; }
        .signatories .sig-name { font-weight: bold; margin: 0; min-height: 1.2em; }
        .signatories .sig-role { font-style: italic; margin: 0; min-height: 1.2em; }
        .signatories [contenteditable] { outline: none; }
        .signatories [contenteditable]:hover, .signatories [contenteditable]:focus { background: #fef9c3; box-shadow: 0 0 0 2px #fde68a; border-radius: 2px; }
        .toolbar .hint { color: #555; font-size: 12px; }
        @media print {
            .signatories [contenteditable]:hover, .signatories [contenteditable]:focus { background: none; box-shadow: none; }
            body { background: #fff; padding: 0; }
            .toolbar { display: none; }
            .paper { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
<script src="{{ asset('js/nav-history.js') }}?v={{ @filemtime(public_path('js/nav-history.js')) }}"></script>
</head>
<body>
    @php
        $me = auth()->user();
        $sig = [
            'preparedName' => $me->tosPreparedName ?: strtoupper($me->name ?? ''),
            'preparedRole' => $me->tosPreparedRole ?: 'Faculty',
            'approvedName' => $me->tosApprovedName ?: 'MELITO L. HIRONDO CE, MEng',
            'approvedRole' => $me->tosApprovedRole ?: 'Dean, College of Engineering',
        ];
        $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
        $totalHours = $topicRows->sum('contactHours');
        $totalPoints = $topicRows->sum('assignedPoints');
    @endphp
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
        <a data-back href="{{ route('courses.tos.show', [$course, $tos]) }}">← Back to ToS</a>
        <span class="hint">Tip: click a name or title at the bottom to edit it. Changes are saved automatically.</span> <span class="hint" id="sigStatus"></span>
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

        <div class="signatories">
            <div class="sig-prepared">
                <p class="sig-label">Prepared by:</p>
                <p class="sig-name" contenteditable="true" spellcheck="false" data-sig="preparedName">{{ $sig['preparedName'] }}</p>
                <p class="sig-role" contenteditable="true" spellcheck="false" data-sig="preparedRole">{{ $sig['preparedRole'] }}</p>
            </div>

            <div class="sig-approved">
                <p class="sig-label">Approved:</p>
                <p class="sig-name" contenteditable="true" spellcheck="false" data-sig="approvedName">{{ $sig['approvedName'] }}</p>
                <p class="sig-role" contenteditable="true" spellcheck="false" data-sig="approvedRole">{{ $sig['approvedRole'] }}</p>
            </div>
        </div>
    </div>
    <script>
        // Names/titles are editable on the page and saved to the signed-in account,
        // so they come back pre-filled on every print, on any browser.
        (function () {
            var url = @json(route('tos.signatories.save'));
            var token = @json(csrf_token());
            var status = document.getElementById('sigStatus');
            var timer = null;

            function collect() {
                var out = {};
                document.querySelectorAll('[data-sig]').forEach(function (el) {
                    out[el.getAttribute('data-sig')] = el.textContent.replace(/\s+/g, ' ').trim();
                });
                return out;
            }

            function save() {
                if (status) status.textContent = 'Saving...';
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify(collect())
                }).then(function (r) {
                    if (status) status.textContent = r.ok ? 'Saved' : 'Could not save';
                }).catch(function () {
                    if (status) status.textContent = 'Could not save';
                });
            }

            document.querySelectorAll('[data-sig]').forEach(function (el) {
                el.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); el.blur(); } });
                el.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var t = (e.clipboardData || window.clipboardData).getData('text').replace(/\s+/g, ' ');
                    document.execCommand('insertText', false, t);
                });
                el.addEventListener('input', function () {
                    if (status) status.textContent = '';
                    clearTimeout(timer);
                    timer = setTimeout(save, 600);
                });
                el.addEventListener('blur', function () { clearTimeout(timer); if (status && status.textContent === '') save(); });
            });
        })();
    </script>
</body>
</html>
