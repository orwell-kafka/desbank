<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Format Exam - DESBank</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f5f7; margin: 0; padding: 20px; }
        .layout { display: flex; gap: 20px; max-width: 1200px; margin: 0 auto; align-items: flex-start; }
        .settings { width: 320px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); flex-shrink: 0;
                    position: sticky; top: 20px; max-height: calc(100vh - 40px); overflow-y: auto; box-sizing: border-box; overscroll-behavior: contain; }   /* settings scroll on their own; preview stays put */
        .settings h2 { font-size: 16px; color: #1f2937; margin-top: 0; }
        .settings h3 { font-size: 13px; color: #374151; margin-top: 18px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
        .settings label { display: block; margin-top: 12px; font-size: 13px; color: #374151; }
        .settings input, .settings select { width: 100%; padding: 6px; margin-top: 4px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 13px; }
        .settings input[type=file] { padding: 4px 0; }
        .settings .checkbox-line { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 12px; }
        .settings .checkbox-line input { width: auto; margin: 0; }
        .settings button { margin-top: 16px; width: 100%; padding: 8px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        .settings .print-btn { background: #16a34a; margin-top: 8px; }
        .settings textarea { width: 100%; padding: 6px; margin-top: 4px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 12px; font-family: inherit; line-height: 1.45; resize: vertical; }
        .settings .direction-block { margin-top: 10px; }
        .settings .direction-block .direction-head { display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #4b5563; }
        .settings .direction-reset { width: auto; margin: 0; padding: 2px 8px; background: #fff; color: #2563eb; border: 1px solid #d1d5db; font-size: 11px; }
        .settings .direction-reset:hover { background: #eff6ff; }
        .settings .direction-edited { font-size: 11px; color: #b45309; }
        .settings a.print-btn { display: block; box-sizing: border-box; margin-top: 8px; padding: 8px; background: #16a34a; color: #fff; border-radius: 4px; text-align: center; text-decoration: none; font-size: 13px; }
        .settings a.print-btn-alt { background: #2563eb; }
        .pdf-preview { position: relative; width: 100%; min-width: 0; height: calc(100vh - 40px); min-height: 600px; background: #525659; border-radius: 4px; overflow: hidden; }
        .pdf-frame { display: block; width: 100%; height: 100%; border: 0; }
        .preview-wrapper:has(> .pdf-preview) { position: sticky; top: 20px; flex: 1; min-width: 0; padding: 0; background: none; }
        .pdf-status { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; pointer-events: none; }
        .pdf-status[hidden] { display: none; }
        .status { color: #15803d; font-size: 12px; margin-bottom: 8px; }
        .error { color: #b91c1c; font-size: 12px; margin-bottom: 8px; }
        .back-link { display: block; margin-top: 14px; color: #2563eb; text-decoration: none; font-size: 13px; }
        .current-img { max-width: 100px; max-height: 60px; display: block; margin-top: 6px; border: 1px solid #e5e7eb; }
        .hint { font-size: 11px; color: #9ca3af; margin-top: 2px; }

        .preview-wrapper { flex: 1; display: flex; justify-content: center; background: #d1d5db; padding: 20px; border-radius: 8px; }
        .paper { background: #fff; padding: var(--header-top, 50px) 50px 50px; box-sizing: border-box; box-shadow: 0 2px 10px rgba(0,0,0,0.2); position: relative; width: 8.5in; min-height: 11in; overflow: hidden; }
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

        /* Two columns, two rows: Name | Score on top, Section | Date underneath.
           The right column starts about 60% across. Each field is "Label:" followed by its own line. */
        .info-row { display: grid; grid-template-columns: 1.5fr 1fr; column-gap: 36px; row-gap: 16px; font-size: 13px; margin-bottom: 15px; }
        .info-field { display: flex; align-items: flex-end; gap: 6px; }
        .info-line { flex: 1; min-width: 40px; height: 14px; border-bottom: 1px solid #000; }
        .info-half { width: 52%; }   /* Score and Date lines are about half the length of the column */

        .part-header { font-weight: bold; font-size: 13px; margin-top: 20px; margin-bottom: 8px; text-transform: uppercase; }
        .part-direction { font-size: 12px; font-style: italic; margin-bottom: 10px; }
        .part-direction .direction-text { white-space: pre-line; }
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

        /* ---- MarSU letterhead: replicates the Word header/footer. All measurements are in inches
           taken from the .docx (header group 7.829in x 1.0355in; footer group 7.84in x 0.7826in). ---- */
        .paper.has-marsu-header { padding-top: calc(var(--header-top, 30px) + var(--hdr-h, 1.0355in) + 0.25in); }
        .paper.has-marsu-footer { padding-bottom: var(--ftr-pad, 1.45in); }

        .paper > .marsu-header { position: absolute; left: 0.3355in; top: var(--header-top, 30px); width: 7.829in; height: 1.0355in; z-index: 1; }
        .marsu-header > * { position: absolute; display: block; margin: 0; }
        .marsu-header .mh-logo-l { left: 0.734in; top: 0.011in; width: 0.831in; height: 0.909in; }
        .marsu-header .mh-logo-r { left: 6.340in; top: 0.053in; width: 0.838in; height: 0.835in; }
        .marsu-header .mh-rule { left: 0; top: 0.902in; width: 7.829in; height: 0.133in; }
        .marsu-header .mh-text { left: 1.085in; top: 0; width: 5.217in; height: 0.988in; padding: 0.05in 0.1in; box-sizing: border-box; font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 12pt; color: #000; }
        .marsu-header .mh-text p { margin: 0 -0.474in 0 0; text-align: center; line-height: normal; }
        /* ---- College of Engineering letterhead (from TOS_Management_Finals.docx). Coordinates are inches
           measured from the left edge of the page and from the top of the header group / footer group. ---- */
        .paper > .coe-header { position: absolute; left: 0; top: var(--header-top, 12px); width: 8.5in; height: 1.15in; z-index: 1; font-family: Cambria, Caladea, 'Cambria Math', 'Times New Roman', serif; color: #000; }
        .coe-header > * { position: absolute; display: block; margin: 0; }
        .coe-header .ch-logo { left: 0.729in; top: 0.1028in; width: 0.885in; height: 0.885in; }
        .coe-header .ch-vline { left: 1.6458in; top: 0.03125in; width: 0; height: 1.0938in; border-left: 1pt solid #F79646; }
        .coe-header .ch-top { left: 1.6458in; top: 0; width: 5.552in; padding: 0.05in 0.1in 0; box-sizing: border-box; font-size: 10pt; line-height: 1.15; }
        .coe-header .ch-text { left: 1.6458in; top: 0.198in; width: 6.222in; padding: 0.05in 0.1in 0; box-sizing: border-box; font-size: 7pt; line-height: 1.15; }
        .coe-header .ch-text p, .coe-header .ch-top p { margin: 0; }
        .coe-header .ch-text p:first-child { font-size: 18pt; font-weight: bold; line-height: 1.15; }
        .coe-header .ch-text p:nth-child(2) { font-weight: bold; }
        .coe-header .ch-link { color: #0000FF; text-decoration: underline; }
        .coe-header .ch-bar { left: 0.75in; top: 1.0625in; width: 7.198in; height: 0.083in; background: #F79646; }
        .paper > .coe-footer { position: absolute; left: 0; bottom: 0; width: 8.5in; height: 0.95in; z-index: 1; font-family: 'Cambria Math', Cambria, Caladea, 'Times New Roman', serif; color: #000; }
        .coe-footer > * { position: absolute; display: block; margin: 0; }
        .coe-footer .cf-rule { left: 0.646in; top: 0.0757in; width: 7.24in; height: 0.0694in; border-top: 3pt solid #F79646; border-bottom: 1pt solid #F79646; box-sizing: border-box; }
        .coe-footer .cf-seal { left: 0.611in; top: 0.185in; width: 0.559in; height: 0.548in; }
        .coe-footer .cf-iso { left: 1.143in; top: 0.262in; width: 1.021in; height: 0.373in; }
        .coe-footer .cf-text { left: 2.26in; top: 0.107in; width: 5.708in; padding: 0.05in 0.1in 0; box-sizing: border-box; font-size: 8pt; line-height: 1.15; }
        .coe-footer .cf-text p { margin: 0; text-align: justify; }
        .coe-footer .cf-text p.cf-tagline { text-align: center; font-weight: bold; font-size: 9pt; }
        .paper.has-coe-footer { --ftr-pad: 1.2in; }
        .marsu-title { text-align: center; font-size: 14px; font-weight: bold; text-transform: uppercase; margin: 0 0 14px; }

        .paper > .marsu-footer { position: absolute; left: 0.339in; bottom: 0.317in; width: 7.84in; height: 0.7826in; z-index: 1; }
        .marsu-footer > * { position: absolute; display: block; margin: 0; }
        .marsu-footer .mf-rule { left: 0; top: -0.0347in; width: 7.84in; height: 0.0694in; }
        .marsu-footer .mf-iso { left: 1.0315in; top: 0.224in; width: 1.021in; height: 0.373in; }
        .marsu-footer .mf-seal { left: 0.1915in; top: 0.043in; width: 0.676in; height: 0.74in; }
        .marsu-footer .mf-text { left: 2.1485in; top: 0.16in; width: 5.364in; padding: 0.05in 0.1in; box-sizing: border-box; font-family: 'Calibri Light', 'Calibri', 'Carlito', 'Segoe UI', Arial, sans-serif; font-size: 7pt; color: #000; }
        .marsu-footer .mf-text p { margin: 0; line-height: normal; }

        /* ---- Print layout ----
           @page margin 0 stops the browser printing its own date/title/URL/page-number text.
           The page margins are rebuilt with a table: the empty <thead>/<tfoot> rows repeat on every
           printed page, so every page keeps a top and bottom margin. The MarSU header and footer are
           positioned on the first page only. ---- */
        /* The watermark must never take up space (".paper > *" would otherwise make it position:relative
           and push the questions down). On paper it is fixed, so it repeats centered on every page. */
        .paper > .watermark-text, .paper > .watermark-img { position: absolute; z-index: 0; }
        .sheet { width: 100%; border-collapse: collapse; border-spacing: 0; }
        .sheet > thead > tr > td, .sheet > tbody > tr > td, .sheet > tfoot > tr > td { padding: 0; vertical-align: top; }
        .sheet-top, .sheet-bottom, .first-page-gap { height: 0; }

        @media print {
            body { background: #fff; padding: 0; }
            .paper, .paper.has-marsu-header, .paper.has-marsu-footer { padding: 0 50px; overflow: visible; }   /* overflow:hidden would clip the last page's footer */
            .pdf-preview { display: none; }
            .paper > .watermark-text, .paper > .watermark-img { position: fixed; }
            .sheet-top, .sheet-bottom { height: 0.5in; }
            .paper.has-marsu-footer .sheet-bottom { height: 1.3in; }
            .paper.has-coe-footer .sheet-bottom { height: 1.05in; }
            .first-page-gap { height: max(0px, calc(var(--header-top, 50px) - 0.5in)); }
            .paper.has-marsu-header .first-page-gap { height: max(0px, calc(var(--header-top, 30px) + var(--hdr-h, 1.0355in) + 0.25in - 0.5in)); }
            .paper > .marsu-footer { top: calc(var(--page-h, 11in) - 1.0996in); bottom: auto; }
            .paper > .coe-footer { top: calc(var(--page-h, 11in) - 0.95in); bottom: auto; }
            .settings, .back-link { display: none; }
            .layout { display: block; }
            .preview-wrapper { background: none; padding: 0; }
            .paper { box-shadow: none; margin: 0; }
        }
    </style>
    <style>@page { size: {{ ($exam->paperSize ?? 'Letter') === 'Legal' ? '8.5in 14in' : '8.5in 11in' }}; margin: 0; }</style>
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

    // MarSU letterhead: built-in wording copied from the Word template; editable per exam.
    $marsuHeaderDefault = "Marinduque State University\nCollege of Engineering\nBachelor of Science in Computer Engineering\nPanfilo M. Manguera Sr. Rd., Tanza, Boac, Marinduque";
    $marsuFooterDefault = "Vision: By 2030, MarSU is a globally recognized university, fostering academic excellence through research and innovation.\nMission: MarSU provides excellence in instruction, research, extension and production, responsive to the needs of the 21st century education in pursuit of global transformation.";
    $splitLines = fn ($text) => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)), fn ($l) => $l !== ''));
    // College of Engineering letterhead: wording copied from the Word file (TOS_Management_Finals.docx).
    // Line 1 sits above the college name; line 2 is the big bold college name; line 3 is bold; the rest are regular.
    $coeHeaderDefault = "Marinduque State University\nCollege of Engineering\nMain Campus Boac/Branches: Sta. Cruz· Torrijos· Gasan\nPanfilo M. Manguera Sr. Rd., Tanza, Boac, Marinduque 4900\nTel.No. (042) 754-0178 Email Address: ceng@marsu.edu.ph\nWebsite: www.mscmarinduque.edu.ph";
    $coeFooterDefault = $marsuFooterDefault . "\nEmpowering minds, transforming lives, and advancing opportunities with a HEART!";
    $headerTpl = $exam->headerTemplate ?? 'classic';
    $footerTpl = $exam->footerTemplate ?? 'simple';
    $headerDefault = $headerTpl === 'coe' ? $coeHeaderDefault : $marsuHeaderDefault;
    $footerDefault = $footerTpl === 'coe' ? $coeFooterDefault : $marsuFooterDefault;
    $headerLines = $splitLines(old('headerLines', $exam->headerLines) ?: $headerDefault);
    $footerLines = $splitLines(old('footerLines', $exam->footerLines) ?: $footerDefault);
    $letterheadDefaults = [
        'header' => ['marsu' => $marsuHeaderDefault, 'coe' => $coeHeaderDefault],
        'footer' => ['marsu' => $marsuFooterDefault, 'coe' => $coeFooterDefault],
    ];
    $isLetterheadHdr = in_array($headerTpl, ['marsu', 'coe'], true);
    $isLetterheadFtr = in_array($footerTpl, ['marsu', 'coe'], true);
    // Turns e-mail addresses and www. addresses in a header line into blue underlined text, like the Word file.
    $coeLink = fn ($line) => preg_replace('/([A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}|www\.[A-Za-z0-9.-]+\.[A-Za-z]{2,})/', '<span class="ch-link">$1</span>', e($line));
    $headerTop = (int) ($exam->headerTop ?? 50);

    // printMode = the page Chrome turns into the PDF (no settings panel, images embedded).
    $printMode = $printMode ?? false;
    $pdfAvailable = $pdfAvailable ?? false;
    $src = fn ($path) => $printMode ? \App\Services\ExamPdf::dataUri($path) : asset($path);
@endphp

    <div class="layout">
        @unless ($printMode)
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
                    <option value="Letter" {{ old('paperSize', $exam->paperSize) == 'Letter' ? 'selected' : '' }}>Letter (Short)</option>
                    <option value="Legal" {{ old('paperSize', $exam->paperSize) == 'Legal' ? 'selected' : '' }}>Legal (Long)</option>
                </select>

                <label>School Name</label>
                <input type="text" name="schoolName" value="{{ old('schoolName', $exam->schoolName) }}">

                <label>Department</label>
                <input type="text" name="department" value="{{ old('department', $exam->department) }}">

                <label>Columns per Section</label>
                @foreach ($presentTypes as $type)
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:6px;">
                        <span style="font-size:12px; color:#4b5563;">{{ $type }}</span>
                        <select name="columns[{{ $type }}]" style="width:70px;">
                            <option value="1" {{ (old('columns.' . $type, $columnsPerPart[$type] ?? 1)) == 1 ? 'selected' : '' }}>1</option>
                            <option value="2" {{ (old('columns.' . $type, $columnsPerPart[$type] ?? 1)) == 2 ? 'selected' : '' }}>2</option>
                            <option value="3" {{ (old('columns.' . $type, $columnsPerPart[$type] ?? 1)) == 3 ? 'selected' : '' }}>3</option>
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

                <h3>Section Directions</h3>
                <div class="hint" style="margin-bottom:6px;">Edit the instruction printed under each part. Leave a box empty, or press Reset, to use the default wording.</div>
                @foreach ($presentTypes as $type)
                    @php $directionValue = old('directions.' . $type, $directions[$type] ?? ''); @endphp
                    <div class="direction-block">
                        <div class="direction-head">
                            <label for="direction-{{ $loop->index }}" style="margin:0; font-size:12px;">{{ $type }}</label>
                            <button type="button" class="direction-reset" data-target="direction-{{ $loop->index }}">Reset</button>
                        </div>
                        <textarea id="direction-{{ $loop->index }}" name="directions[{{ $type }}]" rows="3" maxlength="600" data-type="{{ $type }}" data-default="{{ $defaultDirections[$type] ?? '' }}">{{ $directionValue }}</textarea>
                    </div>
                @endforeach

                <h3>Header Template</h3>
                <select name="headerTemplate">
                    <option value="classic" {{ old('headerTemplate', $exam->headerTemplate) == 'classic' ? 'selected' : '' }}>Classic (double border, centered)</option>
                    <option value="formal" {{ old('headerTemplate', $exam->headerTemplate) == 'formal' ? 'selected' : '' }}>Formal (boxed border)</option>
                    <option value="minimal" {{ old('headerTemplate', $exam->headerTemplate) == 'minimal' ? 'selected' : '' }}>Minimal (left-aligned)</option>
                    <option value="marsu" {{ old('headerTemplate', $exam->headerTemplate) == 'marsu' ? 'selected' : '' }}>MarSU letterhead (logos, 4 lines, orange rule)</option>
                    <option value="coe" {{ old('headerTemplate', $exam->headerTemplate) == 'coe' ? 'selected' : '' }}>College of Engineering letterhead (logo, 6 lines, orange bar)</option>
                </select>
                <div class="hint">Pre-designed layout — school name/department text only, no custom logo.</div>

                <label for="headerTop">Header distance from top (px)</label>
                <input type="number" id="headerTop" name="headerTop" min="0" max="200" value="{{ old('headerTop', $headerTop) }}">
                <div class="hint">Moves the whole header down from the top edge of the paper. The MarSU letterhead matches the Word file at 30; the College of Engineering letterhead at 12.</div>

                <label for="headerLines">Letterhead lines (MarSU / College of Engineering)</label>
                <textarea id="headerLines" name="headerLines" rows="6" maxlength="600" style="width:100%; box-sizing:border-box; padding:6px; margin-top:4px; border:1px solid #d1d5db; border-radius:4px; font:inherit; font-size:13px;">{{ old('headerLines', $exam->headerLines ?: $headerDefault) }}</textarea>
                <div class="hint">One line per row. MarSU: centered between the two logos. College of Engineering: line 2 is the big college name, line 3 is bold.</div>

                <h3>Footer Text</h3>
                <input type="text" name="footerText" value="{{ old('footerText', $exam->footerText) }}">

                <h3>Footer Template</h3>
                <select name="footerTemplate">
                    <option value="simple" {{ old('footerTemplate', $exam->footerTemplate) == 'simple' ? 'selected' : '' }}>Simple (centered, thin line)</option>
                    <option value="paged" {{ old('footerTemplate', $exam->footerTemplate) == 'paged' ? 'selected' : '' }}>Paged (left/right split, bold line)</option>
                    <option value="minimal" {{ old('footerTemplate', $exam->footerTemplate) == 'minimal' ? 'selected' : '' }}>Minimal (small, no line)</option>
                    <option value="marsu" {{ old('footerTemplate', $exam->footerTemplate) == 'marsu' ? 'selected' : '' }}>MarSU footer (orange rule, logos, vision/mission)</option>
                    <option value="coe" {{ old('footerTemplate', $exam->footerTemplate) == 'coe' ? 'selected' : '' }}>College of Engineering footer (quality award, ISO badge, vision/mission, tagline)</option>
                </select>

                <label for="footerLines">Footer lines (MarSU / College of Engineering)</label>
                <textarea id="footerLines" name="footerLines" rows="7" maxlength="900" style="width:100%; box-sizing:border-box; padding:6px; margin-top:4px; border:1px solid #d1d5db; border-radius:4px; font:inherit; font-size:13px;">{{ old('footerLines', $exam->footerLines ?: $footerDefault) }}</textarea>
                <div class="hint">One paragraph per row. A leading label such as &ldquo;Vision:&rdquo; is printed in bold. On the College of Engineering footer, a row without a label is printed as the centered bold tagline.</div>

                <h3>Watermark Text</h3>
                <input type="text" name="watermarkText" value="{{ old('watermarkText', $exam->watermarkText) }}">

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

            <script>
                (function () {
                    // Live preview: typing in a direction box updates that part on the paper right away.
                    // Nothing is saved until "Update preview" is pressed.
                    const boxes = document.querySelectorAll('textarea[data-type]');
                    function paperText(type) {
                        return Array.from(document.querySelectorAll('.part-direction[data-type]'))
                            .find(function (el) { return el.dataset.type === type; });
                    }
                    function sync(box) {
                        const target = paperText(box.dataset.type);
                        if (!target) return;
                        const text = box.value.trim() === '' ? box.dataset.default : box.value.trim();
                        target.querySelector('.direction-text').textContent = text;
                    }
                    boxes.forEach(function (box) { box.addEventListener('input', function () { sync(box); }); });
                    document.querySelectorAll('.direction-reset').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const box = document.getElementById(btn.dataset.target);
                            box.value = box.dataset.default;
                            sync(box);
                        });
                    });
                })();
            </script>

            @if ($pdfAvailable)
                <a class="print-btn" href="{{ route('courses.exams.format.pdf', [$course, $exam]) }}?download=1">Download PDF</a>
                <a class="print-btn print-btn-alt" href="{{ route('courses.exams.format.pdf', [$course, $exam]) }}" target="_blank" rel="noopener">Open PDF to print</a>
            @else
                <div class="hint">Exact PDF preview is off. Install Google Chrome or Microsoft Edge on this computer, or set CHROME_PATH in .env.</div>
                <button type="button" class="print-btn" onclick="window.print()">Print / Save as PDF</button>
            @endif

            <a href="{{ route('courses.exams.show', [$course, $exam]) }}" class="back-link">&larr; Back to Exam</a>
        </div>
        @endunless

        <div class="preview-wrapper">
            @if (! $printMode && $pdfAvailable)
                <div class="pdf-preview">
                    <div class="pdf-status" id="pdfStatus" role="status">Building the exact print preview&hellip;</div>
                    <iframe id="pdfFrame" class="pdf-frame" title="Exact print preview of the exam (PDF)" src="{{ route('courses.exams.format.pdf', [$course, $exam]) }}?v={{ now()->timestamp }}#view=FitH"></iframe>
                </div>
                <script>
                    document.getElementById('pdfFrame').addEventListener('load', function () {
                        document.getElementById('pdfStatus').hidden = true;
                    });
                </script>
            @else
            <div class="paper {{ strtolower($exam->paperSize ?? 'letter') }} {{ $isLetterheadHdr ? 'has-marsu-header' : '' }} {{ $isLetterheadFtr ? 'has-marsu-footer' : '' }} {{ $footerTpl === 'coe' ? 'has-coe-footer' : '' }}" style="--header-top: {{ $headerTop }}px; --hdr-h: {{ $headerTpl === 'coe' ? '1.15in' : '1.0355in' }}; --page-h: {{ ($exam->paperSize ?? 'Letter') === 'Legal' ? '14in' : '11in' }};">
                @if ($exam->watermarkImagePath)
                    <img src="{{ $src('storage/' . $exam->watermarkImagePath) }}" class="watermark-img">
                @elseif ($exam->watermarkText)
                    <div class="watermark-text">{{ $exam->watermarkText }}</div>
                @endif

                @if ($headerTpl === 'coe')
                    <div class="coe-header">
                        <img class="ch-logo" src="{{ $src('images/exam-templates/marsu/coe-logo.png') }}" alt="">
                        <div class="ch-vline"></div>
                        <div class="ch-top">
                            @foreach (array_slice($headerLines, 0, 1) as $line)
                                <p>{!! $coeLink($line) !!}</p>
                            @endforeach
                        </div>
                        <div class="ch-text">
                            @foreach (array_slice($headerLines, 1) as $line)
                                <p>{!! $coeLink($line) !!}</p>
                            @endforeach
                        </div>
                        <div class="ch-bar"></div>
                    </div>
                @endif
                @if ($headerTpl === 'marsu')
                    <div class="marsu-header">
                        <img class="mh-logo-r" src="{{ $src('images/exam-templates/marsu/college-logo.png') }}" alt="">
                        <div class="mh-text">
                            @foreach ($headerLines as $line)
                                <p>{{ $line }}</p>
                            @endforeach
                        </div>
                        <img class="mh-rule" src="{{ $src('images/exam-templates/marsu/rule.png') }}" alt="">
                        <img class="mh-logo-l" src="{{ $src('images/exam-templates/marsu/seal.jpg') }}" alt="">
                    </div>
                @endif

                <table class="sheet" role="presentation">
                    <thead><tr><td><div class="sheet-top"></div></td></tr></thead>
                    <tfoot><tr><td><div class="sheet-bottom"></div></td></tr></tfoot>
                    <tbody><tr><td>
                <div class="first-page-gap"></div>
                @if ($isLetterheadHdr)
                    <div class="marsu-title">{{ $tos->title ?? 'Exam' }} — {{ $tos->examType ?? '' }}</div>
                @else
                    @php $headerClass = 'header-' . ($exam->headerTemplate ?? 'classic'); @endphp
                    <div class="{{ $headerClass }}">
                        <img src="{{ $src($headerImageMap[$exam->headerTemplate ?? 'classic']) }}">
                        <div class="school">{{ $exam->schoolName ?? 'School Name' }}</div>
                        <div class="dept">{{ $exam->department ?? 'Department' }}</div>
                        <div class="title">{{ $tos->title ?? 'Exam' }} — {{ $tos->examType ?? '' }}</div>
                    </div>
                @endif

                <div class="info-row">
                    <div class="info-field"><span>Name:</span><span class="info-line"></span></div>
                    <div class="info-field info-half"><span>Score:</span><span class="info-line"></span></div>
                    <div class="info-field"><span>Section:</span><span class="info-line"></span></div>
                    <div class="info-field info-half"><span>Date:</span><span class="info-line"></span></div>
                </div>

               @php $partNum = 1; $romanParts = ['I','II','III','IV','V','VI']; $letters = ['A','B','C','D','E','F','G','H','I','J','K','L']; @endphp
                @foreach ($questions as $type => $group)
                    @php $itemCounter = 1; @endphp
                    <div class="part-header">
                        PART {{ $romanParts[$partNum - 1] ?? $partNum }} — {{ strtoupper($type) }}
                        (ITEMS 1–{{ $group->count() }})
                    </div>
                    <div class="part-direction" data-type="{{ $type }}">Direction: <span class="direction-text">{{ $directions[$type] ?? '' }}</span></div>

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

                @if (! $isLetterheadFtr && $exam->footerText)
                    @php $footerClass = 'footer-' . ($exam->footerTemplate ?? 'simple'); @endphp
                    <img src="{{ $src($footerImageMap[$exam->footerTemplate ?? 'simple']) }}" style="width:100%; display:block; margin-top:30px;">
                    @if ($exam->footerTemplate == 'paged')
                        <div class="{{ $footerClass }}" style="margin-top:4px; border-top:none;">
                            <span>{{ $exam->footerText }}</span>
                            <span>Page 1</span>
                        </div>
                    @else
                        <div class="{{ $footerClass }}" style="margin-top:4px; border-top:none;">{{ $exam->footerText }}</div>
                    @endif
                @endif
                    </td></tr></tbody>
                </table>

                @if ($footerTpl === 'coe')
                    <div class="coe-footer">
                        <div class="cf-rule"></div>
                        <img class="cf-seal" src="{{ $src('images/exam-templates/marsu/coe-pqa.jpg') }}" alt="">
                        <img class="cf-iso" src="{{ $src('images/exam-templates/marsu/coe-iso.jpg') }}" alt="">
                        <div class="cf-text">
                            @foreach ($footerLines as $line)
                                @php $hasLabel = preg_match('/^([^:]{1,24}:)\s*(.*)$/u', $line, $m); @endphp
                                @if ($hasLabel)
                                    <p><strong>{{ $m[1] }}</strong> {{ $m[2] }}</p>
                                @else
                                    <p class="cf-tagline">{{ $line }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($footerTpl === 'marsu')
                    <div class="marsu-footer">
                        <svg class="mf-rule" viewBox="0 0 752.64 6.667" preserveAspectRatio="none" aria-hidden="true"><rect x="0" y="0" width="752.64" height="4" fill="#F79646"/><rect x="0" y="5.333" width="752.64" height="1.334" fill="#F79646"/></svg>
                        <img class="mf-iso" src="{{ $src('images/exam-templates/marsu/iso-badge.jpg') }}" alt="">
                        <img class="mf-seal" src="{{ $src('images/exam-templates/marsu/seal.jpg') }}" alt="">
                        <div class="mf-text">
                            @foreach ($footerLines as $line)
                                @php $hasLabel = preg_match('/^([^:]{1,24}:)\s*(.*)$/u', $line, $m); @endphp
                                <p>@if ($hasLabel)<strong>{{ $m[1] }}</strong> {{ $m[2] }}@else{{ $line }}@endif</p>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    @unless ($printMode)
    <script>
        // Choosing a letterhead moves it to the distance used in its Word file (MarSU 30 px, College of
        // Engineering 12 px) unless the distance was already changed by hand, and swaps the built-in wording
        // in the line boxes (only when they still hold built-in wording, never when they were edited).
        (function () {
            var hdr = document.querySelector('select[name="headerTemplate"]');
            var ftr = document.querySelector('select[name="footerTemplate"]');
            var top = document.getElementById('headerTop');
            var hLines = document.getElementById('headerLines');
            var fLines = document.getElementById('footerLines');
            var defaults = @json($letterheadDefaults);
            var norm = function (t) { return (t || '').replace(/\r/g, '').trim(); };
            var swap = function (box, set, key) {
                if (!box || !set[key]) return;
                var cur = norm(box.value);
                if (cur === '' || cur === norm(set.marsu) || cur === norm(set.coe)) box.value = set[key];
            };
            if (hdr) hdr.addEventListener('change', function () {
                if (top && (top.value === '50' || top.value === '30' || top.value === '12')) {
                    if (hdr.value === 'marsu') top.value = '30';
                    if (hdr.value === 'coe') top.value = '12';
                }
                swap(hLines, defaults.header, hdr.value);
            });
            if (ftr) ftr.addEventListener('change', function () { swap(fLines, defaults.footer, ftr.value); });
        })();
    </script>
    @endunless
</body>
</html>