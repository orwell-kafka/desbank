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
        /* Update Preview stays pinned to the bottom-right of the settings panel while it scrolls. */
        .settings { scroll-padding-bottom: 72px; }
        .settings .save-bar { position: sticky; bottom: 0; z-index: 5; display: flex; justify-content: flex-end; margin: 16px -20px 0; padding: 12px 20px; background: #fff; border-top: 1px solid #e5e7eb; box-shadow: 0 -8px 14px -10px rgba(0,0,0,.25); }
        .settings .save-bar button { width: auto; min-width: 150px; margin: 0; padding: 9px 20px; font-size: 13px; font-weight: 600; }
        .settings .print-btn { background: #16a34a; margin-top: 8px; }
        .settings textarea { width: 100%; padding: 6px; margin-top: 4px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; font-size: 12px; font-family: inherit; line-height: 1.45; resize: vertical; }
        .settings .direction-reset { width: auto; margin: 0; padding: 2px 8px; background: #fff; color: #2563eb; border: 1px solid #d1d5db; font-size: 11px; }
        .settings .direction-reset:hover { background: #eff6ff; }
        /* Question types: one "Customize" button opens a pop-up with a tab per type (columns, answer line, direction). */
        .settings .type-customize { display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; margin: 0; padding: 9px 12px; background: #fff; color: #2563eb; border: 1px solid #2563eb; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .settings .type-customize:hover { background: #eff6ff; }
        .type-dialog { width: min(860px, calc(100vw - 32px)); height: min(580px, calc(100vh - 40px)); max-height: none; padding: 0; border: 0; border-radius: 10px; color: #1f2937; background: #fff; box-shadow: 0 24px 60px rgba(15, 23, 42, .35); overflow: hidden; }
        .type-dialog[open] { display: flex; flex-direction: column; }
        .type-dialog::backdrop { background: rgba(15, 23, 42, .5); }
        .settings .td-head { display: flex; align-items: center; justify-content: space-between; flex: 0 0 auto; padding: 14px 12px 12px 22px; border-bottom: 1px solid #e5e7eb; }
        .settings .td-head h3 { margin: 0; padding: 0; border: 0; font-size: 16px; color: #111827; }
        .settings .td-close { width: 34px; height: 34px; margin: 0; padding: 0; background: transparent; color: #4b5563; border: 0; border-radius: 6px; font-size: 22px; line-height: 1; cursor: pointer; }
        .settings .td-close:hover { background: #f3f4f6; color: #111827; }
        .td-main { display: grid; grid-template-columns: 230px minmax(0, 1fr); flex: 1 1 auto; min-height: 0; }
        .settings .td-tabs { display: flex; flex-direction: column; gap: 2px; padding: 10px; overflow-y: auto; background: #f8fafc; border-right: 1px solid #e5e7eb; }
        .settings .td-tab { display: block; width: 100%; margin: 0; padding: 10px 12px; text-align: left; background: transparent; color: #374151; border: 0; border-left: 3px solid transparent; border-radius: 6px; cursor: pointer; }
        .settings .td-tab:hover { background: #eef2f7; }
        .settings .td-tab[aria-selected="true"] { background: #fff; border-left-color: #2563eb; box-shadow: 0 1px 3px rgba(15, 23, 42, .12); }
        .settings .td-tab:focus-visible { outline: 2px solid #2563eb; outline-offset: 1px; }
        .td-tab-name { display: block; font-size: 13px; font-weight: 600; color: #111827; }
        .td-tab-sum { display: block; margin-top: 2px; font-size: 11px; color: #6b7280; }
        .td-panels { overflow-y: auto; padding: 18px 26px 24px; }
        .td-panel[hidden] { display: none; }
        .settings .td-panel-title { margin: 0 0 6px; padding: 0; border: 0; font-size: 18px; color: #111827; }
        .td-field { margin: 20px 0 0; padding: 0; border: 0; min-width: 0; }
        .td-field legend, .settings .td-label { display: inline-block; margin: 0 0 8px; padding: 0; font-size: 13px; font-weight: 600; color: #374151; }
        .td-label-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .td-label-row .td-label { margin-bottom: 8px; }
        .td-badge { margin-left: 6px; padding: 1px 7px; border-radius: 999px; background: #eef2f7; color: #4b5563; font-size: 10px; font-weight: 600; letter-spacing: .02em; }
        .td-badge.is-edited { background: #fef3c7; color: #92400e; }
        .td-hint { margin-top: 6px; font-size: 11px; line-height: 1.4; color: #6b7280; }
        .td-seg-group { display: inline-flex; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
        .settings .td-seg { position: relative; display: block; margin: 0; cursor: pointer; }
        .settings .td-seg + .td-seg { border-left: 1px solid #d1d5db; }
        .type-dialog .td-seg input { position: absolute; opacity: 0; width: 100%; height: 100%; margin: 0; padding: 0; cursor: pointer; }
        .td-seg span { display: block; min-width: 54px; padding: 8px 18px; text-align: center; font-size: 14px; font-weight: 600; color: #374151; background: #fff; }
        .td-seg:hover span { background: #f3f4f6; }
        .td-seg input:checked + span { background: #2563eb; color: #fff; }
        .td-seg input:focus-visible + span { outline: 2px solid #1d4ed8; outline-offset: -3px; }
        .td-inline { display: flex; align-items: center; gap: 8px; }
        .type-dialog .td-inline input { width: 96px; margin: 0; padding: 8px 10px; font-size: 14px; }
        .td-inline span { font-size: 13px; color: #6b7280; }
        .type-dialog .td-panels textarea { margin: 0; padding: 9px 10px; font-size: 13px; line-height: 1.5; }
        .settings .td-label-row .direction-reset { padding: 3px 10px; margin: 0 0 8px; border-radius: 6px; }
        .settings .td-foot { display: flex; justify-content: flex-end; gap: 8px; flex: 0 0 auto; padding: 12px 22px; border-top: 1px solid #e5e7eb; background: #fff; }
        .settings .td-btn { width: auto; margin: 0; padding: 8px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; cursor: pointer; }
        .settings .td-btn-alt { background: #fff; color: #374151; border: 1px solid #d1d5db; }
        .settings .td-btn-alt:hover { background: #f3f4f6; }
        .settings .td-btn-ok { background: #2563eb; color: #fff; border: 0; }
        .settings .td-btn-ok:hover { background: #1d4ed8; }
        @media (max-width: 640px) {
            .type-dialog { width: calc(100vw - 16px); height: calc(100vh - 16px); }
            .td-main { grid-template-columns: 1fr; grid-template-rows: auto minmax(0, 1fr); }
            .settings .td-tabs { flex-direction: row; overflow-x: auto; overflow-y: hidden; border-right: 0; border-bottom: 1px solid #e5e7eb; }
            .settings .td-tab { flex: 0 0 auto; width: auto; border-left: 0; border-bottom: 3px solid transparent; }
            .settings .td-tab[aria-selected="true"] { border-bottom-color: #2563eb; }
            .td-panels { padding: 14px 16px 20px; }
        }
        .settings .direction-actions { display: flex; justify-content: flex-end; gap: 6px; margin-top: 6px; }
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
        .info-row { display: grid; grid-template-columns: 1.5fr 1fr; column-gap: 36px; row-gap: 16px; font-size: 12pt; margin-bottom: 15px; }
        .info-field { display: flex; align-items: flex-end; gap: 6px; }
        .info-line { flex: 1; min-width: 40px; height: 14px; border-bottom: 1px solid #000; }
        .info-half { width: 52%; }   /* Score and Date lines are about half the length of the column */

        /* Accredited letterhead: same Name/Score/Section/Date grid; the Score is a box split by a diagonal,
           with the exam's total score (the denominator) filled in the lower-right half. */
        .info-score { display: flex; align-items: flex-end; gap: 8px; }
        .info-score > span { padding-bottom: 0.13in; }
        .info-score .score-box { position: relative; width: 1.4in; height: 0.95in; box-sizing: border-box; border: 1.5px solid #000; }
        .info-score .score-box svg { position: absolute; inset: 0; width: 100%; height: 100%; display: block; }
        .info-score .score-total { position: absolute; right: 8px; bottom: 4px; font-size: 22px; font-weight: bold; line-height: 1; }
        .info-row.info-row-boxed > .info-field:first-child { padding-bottom: 0.13in; }   /* Name line sits level with "Score:" */

        .general-instructions { text-align: justify; margin: 26px 0; font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 12pt; line-height: 1.4; }
        .general-instructions .gi-text { white-space: pre-line; }
        /* "Test I. Multiple Choice (Items 1–3)": the direction below is indented to start under the
           first letter of the question type (column 2), whatever the width of "Test I." / "Test III.". */
        .part-top { display: grid; grid-template-columns: max-content 1fr; column-gap: 0.28em; margin-top: 20px; margin-bottom: 10px; break-inside: avoid; break-after: avoid; font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 12pt; }
        .part-header { display: contents; font-weight: bold; }
        .part-header .ph-num { grid-row: 1; grid-column: 1; }
        .part-header .ph-type { grid-row: 1; grid-column: 2; text-transform: capitalize; }
        .part-direction { grid-row: 2; grid-column: 2; text-align: justify; margin-top: 4px; font-weight: bold; font-style: normal; }
        .part-direction .direction-text { white-space: pre-line; }
        .question-list { columns: var(--cols, 1); column-gap: 30px; }
        .question-item { text-align: justify; font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 12pt; margin-bottom: 10px; break-inside: avoid; }
        /* Hanging indent: a wrapped line starts under the first word of the question, not under the number. */
        .q-row { display: flex; align-items: baseline; }
        .q-lead { flex: 0 0 auto; margin-right: 0.35em; white-space: nowrap; }
        .q-text { flex: 1 1 auto; min-width: 0; overflow-wrap: anywhere; }
        .choices { margin-top: 4px; margin-left: 15px; text-align: left; }
        .choices div { font-size: 12pt; margin-bottom: 2px; }
        /* Choices sit side by side when they all fit on one line; otherwise they stack (see $choicesFit in the view). */
        .choices.choices-row { display: flex; flex-wrap: wrap; column-gap: 28px; row-gap: 2px; }
        .choices.choices-row div { margin-bottom: 0; }
        .essay-lines { margin-top: 8px; height: 100px; }

        /* Blank answer line printed before the item number (True/False, Enumeration, Matching Type).
           Its length comes from --len (inches), set per section in the settings panel. */
        .answer-line { display: inline-block; width: var(--len, 0.8in); height: 0.95em; margin-right: 6px; border-bottom: 1px solid #000; vertical-align: baseline; }
        .answer-line.is-off { display: none; }
        .settings .al-row { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; }
        .settings .al-row span { font-size: 12px; color: #4b5563; }
        .settings .al-row input { width: 80px; margin-top: 0; }

        .matching-layout { display: flex; gap: 30px; margin-top: 8px; }
        .matching-col-a { flex: 1; }
        .matching-col-a .item { text-align: justify; font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 12pt; margin-bottom: 10px; }
        .matching-col-b { flex-shrink: 0; min-width: 160px; padding-left: 20px; }        .matching-col-b .item { font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 12pt; margin-bottom: 8px; }
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

        /* ---- College of Engineering letterhead with accreditation strip, GOAL and "God bless" band.
           Fixed wording; the absolute block is the first-page header, the title block flows below it. ---- */
        .paper > .acc-header { position: absolute; left: 0.3355in; top: var(--header-top, 12px); width: 7.829in; height: 2.3in; z-index: 1; font-family: 'Calibri', 'Carlito', 'Segoe UI', Arial, sans-serif; color: #000; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .acc-header > * { position: absolute; display: block; margin: 0; }
        .acc-header .ah-logo-l { left: 0.45in; top: 0.02in; width: 0.85in; height: 0.926in; }
        .acc-header .ah-logo-r { right: 0.45in; top: 0.04in; width: 0.88in; height: 0.88in; }
        .acc-header .ah-text { left: 1.4in; right: 1.4in; top: 0; text-align: center; font-size: 10pt; line-height: 1.12; }
        .acc-header .ah-text p { margin: 0; }
        .acc-header .ah-text .ah-univ { font-size: 12.5pt; text-transform: uppercase; }
        .acc-header .ah-text .ah-college { font-size: 13pt; font-weight: bold; text-transform: uppercase; }
        .acc-header .ah-accred { left: 0; right: 0; top: 1.14in; height: 0.36in; }
        .acc-header .ah-accred .ah-line { position: absolute; left: 0; right: 0; bottom: 0.07in; border-top: 1pt solid #404040; }
        .acc-header .ah-accred .ah-item { position: absolute; top: 0; bottom: 0; white-space: nowrap; font-size: 6.5pt; font-weight: bold; padding-left: 0.14in; line-height: 1; }
        .acc-header .ah-accred .ah-item span { position: absolute; left: 0.14in; bottom: 0.15in; }
        .acc-header .ah-accred .ah-item::before { content: ''; position: absolute; left: 0; bottom: 0.02in; width: 0.1in; height: 0.1in; box-sizing: border-box; border: 1.2pt solid #6b6b6b; border-radius: 50%; background: #f1f1f1; }
        .acc-header .ah-band { left: -0.3355in; width: 8.5in; top: 1.52in; height: 0.11in; background: linear-gradient(to bottom, #fbd2a8 0 22%, #F79646 22% 78%, #fbd2a8 78%); }
        .acc-header .ah-goal { left: 0.12in; right: 0.12in; top: 1.67in; font-size: 8pt; line-height: 1.15; text-align: justify; }
        .acc-header .ah-discipline { left: 0; right: 0; top: 2.1in; text-align: center; font-size: 9pt; font-weight: bold; }
        .paper.has-acc-header { padding-top: calc(var(--header-top, 12px) + var(--hdr-h, 2.3in)); }
        .acc-title { font-family: 'Calibri', 'Carlito', 'Segoe UI', Arial, sans-serif; text-align: center; border-top: 1pt solid #000; padding-top: 4pt; margin: 0 0 30px; }
        .acc-title .at-exam { font-size: 14pt; font-weight: bold; text-transform: uppercase; line-height: 1.2; }
        .acc-title .at-course, .acc-title .at-term { font-size: 13pt; line-height: 1.25; }
        .acc-title .at-blessing { display: flex; justify-content: space-between; margin-top: 8pt; padding: 2pt 0; border-top: 3px double #000; border-bottom: 3px double #000; font-size: 7pt; font-weight: bold; white-space: nowrap; overflow: hidden; }

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
            .paper.has-acc-header .first-page-gap { height: max(0px, calc(var(--header-top, 12px) + var(--hdr-h, 2.3in) - 0.5in)); }
            .paper > .marsu-footer { top: calc(var(--page-h, 11in) - 1.0996in); bottom: auto; }
            .paper > .coe-footer { top: calc(var(--page-h, 11in) - 0.95in); bottom: auto; }
            .settings, .back-link { display: none; }
            .layout { display: block; }
            .preview-wrapper { background: none; padding: 0; }
            .paper { box-shadow: none; margin: 0; }
        }
    </style>
    <style>@page { size: {{ ($exam->paperSize ?? 'Letter') === 'Legal' ? '8.5in 14in' : '8.5in 11in' }}; margin: 0; }</style>
<script src="{{ asset('js/nav-history.js') }}?v={{ @filemtime(public_path('js/nav-history.js')) }}"></script>
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

    // MarSU letterhead: built-in wording copied from the Word template (fixed, not editable).
    $marsuHeaderDefault = "Marinduque State University\nCollege of Engineering\nBachelor of Science in Computer Engineering\nPanfilo M. Manguera Sr. Rd., Tanza, Boac, Marinduque";
    $marsuFooterDefault = "Vision: By 2030, MarSU is a globally recognized university, fostering academic excellence through research and innovation.\nMission: MarSU provides excellence in instruction, research, extension and production, responsive to the needs of the 21st century education in pursuit of global transformation.";
    $splitLines = fn ($text) => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text)), fn ($l) => $l !== ''));
    // College of Engineering letterhead: wording copied from the Word file (TOS_Management_Finals.docx).
    // Line 1 sits above the college name; line 2 is the big bold college name; line 3 is bold; the rest are regular.
    $coeHeaderDefault = "Marinduque State University\nCollege of Engineering\nMain Campus Boac/Branches: Sta. Cruz· Torrijos· Gasan\nPanfilo M. Manguera Sr. Rd., Tanza, Boac, Marinduque 4900\nTel.No. (042) 754-0178 Email Address: ceng@marsu.edu.ph\nWebsite: www.mscmarinduque.edu.ph";
    $coeFooterDefault = $marsuFooterDefault . "\nEmpowering minds, transforming lives, and advancing opportunities with a HEART!";
    // The Classic and Formal headers are no longer offered; exams that still have one (or none) use the newest header.
    $headerTpl = in_array($exam->headerTemplate, ['minimal', 'marsu', 'coe', 'accredited'], true) ? $exam->headerTemplate : 'accredited';
    $footerTpl = $exam->footerTemplate ?? 'simple';
    // College of Engineering letterhead with accreditation strip (copied from the printed exam header).
    $accHeaderDefault = "Marinduque State University\nCollege of Engineering\nComm. E. M. Reyes, Sr. Bldg.\nPanfilo M. Manguera, Sr. Rd.\nTanza, Boac, Marinduque\nTel. No. (042)754-0178";
    $accAccreditations = [
        ['BSCE- Level III AACCUP Re-Accredited', 0],
        ['BSEE- Level II AACCUP Re-Accredited', 1.78],
        ['BSECE-Level II Re-Accredited', 3.55],
        ['BSCpE-Level II Re- Accredited', 5.03],
        ['BS ME', 6.72],
    ];
    $accGoal = "Become the leading producer of engineering professionals, who are lifelong learners, with humane values and technologically-advanced knowledge and skills, through its curricular programs designed to meet clients' and stakeholders' highest level of satisfaction in terms of quality of instruction, scientific researchers and research-based extension projects.";
    $accDiscipline = 'D.I.S.C.I.P.L.I.N.E. is the foundation of success in engineering.';
    $headerDefault = match ($headerTpl) { 'coe' => $coeHeaderDefault, 'accredited' => $accHeaderDefault, default => $marsuHeaderDefault };
    $footerDefault = $footerTpl === 'coe' ? $coeFooterDefault : $marsuFooterDefault;
    // Header/footer wording is fixed: always the built-in text, never user-edited.
    $headerLines = $splitLines($headerDefault);
    $footerLines = $splitLines($footerDefault);
    $isLetterheadHdr = in_array($headerTpl, ['marsu', 'coe', 'accredited'], true);
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

                <h3>Question Types</h3>
                <div class="hint" style="margin-bottom:8px;">Columns, answer lines and directions for every question type.</div>
                @php
                    $startIdx = 0; $hasTypeError = false;
                    foreach ($presentTypes as $i => $t) {
                        if ($errors->has('directions.' . $t) || $errors->has('answerLines.' . $t) || $errors->has('columns.' . $t)) { $startIdx = $i; $hasTypeError = true; break; }
                    }
                @endphp
                <button type="button" class="type-customize" data-open="type-dialog" aria-haspopup="dialog">
                    <svg viewBox="0 0 20 20" width="16" height="16" aria-hidden="true" focusable="false"><path d="M3 5h9M16 5h1M3 10h2M9 10h8M3 15h9M16 15h1" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><circle cx="14" cy="5" r="2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="7" cy="10" r="2" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="14" cy="15" r="2" fill="none" stroke="currentColor" stroke-width="1.6"/></svg>
                    Customize
                </button>

                <dialog class="type-dialog" id="type-dialog" aria-labelledby="type-dialog-title" data-start="{{ $startIdx }}" {{ $hasTypeError ? 'data-autoopen' : '' }}>
                    <div class="td-head">
                        <h3 id="type-dialog-title">Customize question types</h3>
                        <button type="button" class="td-close" data-close aria-label="Close">&times;</button>
                    </div>
                    <div class="td-main">
                        <div class="td-tabs" role="tablist" aria-orientation="vertical" aria-label="Question types">
                            @foreach ($presentTypes as $type)
                                <button type="button" role="tab" class="td-tab" id="td-tab-{{ $loop->index }}" aria-controls="td-panel-{{ $loop->index }}" aria-selected="false" tabindex="-1">
                                    <span class="td-tab-name">{{ $type }}</span>
                                    <span class="td-tab-sum"></span>
                                </button>
                            @endforeach
                        </div>
                        <div class="td-panels">
                            @foreach ($presentTypes as $type)
                                @php
                                    $idx = $loop->index;
                                    $colsVal = (int) old('columns.' . $type, $columnsPerPart[$type] ?? 1);
                                    $hasLine = in_array($type, $answerLineTypes, true);
                                    $alVal = old('answerLines.' . $type, $answerLines[$type] ?? $answerLineDefault);
                                    $directionValue = old('directions.' . $type, $directions[$type] ?? '');
                                @endphp
                                <section class="td-panel" role="tabpanel" id="td-panel-{{ $idx }}" aria-labelledby="td-tab-{{ $idx }}" hidden>
                                    <h4 class="td-panel-title">{{ $type }}</h4>

                                    <fieldset class="td-field">
                                        <legend>Columns</legend>
                                        <div class="td-seg-group">
                                            @foreach ([1, 2, 3] as $n)
                                                <label class="td-seg"><input type="radio" name="columns[{{ $type }}]" value="{{ $n }}" {{ $colsVal === $n ? 'checked' : '' }}><span>{{ $n }}</span></label>
                                            @endforeach
                                        </div>
                                    </fieldset>

                                    @if ($hasLine)
                                        <div class="td-field">
                                            <label class="td-label" for="answerline-{{ $idx }}">Answer line length</label>
                                            <div class="td-inline">
                                                <input type="number" id="answerline-{{ $idx }}" name="answerLines[{{ $type }}]" data-answer-line="{{ $type }}" data-default="{{ $answerLineDefault }}" min="0" max="4" step="0.1" value="{{ $alVal }}">
                                                <span>inches</span>
                                            </div>
                                            <div class="td-hint">Printed before the item number. Use 0 to hide it, or leave empty for the default ({{ rtrim(rtrim(number_format($answerLineDefault, 1), '0'), '.') }} in).</div>
                                        </div>
                                    @endif

                                    <div class="td-field">
                                        <div class="td-label-row">
                                            <label class="td-label" for="direction-{{ $idx }}">Direction <span class="td-badge"></span></label>
                                            <button type="button" class="direction-reset" data-target="direction-{{ $idx }}">Reset to default</button>
                                        </div>
                                        <textarea id="direction-{{ $idx }}" name="directions[{{ $type }}]" rows="5" maxlength="600" aria-label="{{ $type }} direction" data-type="{{ $type }}" data-default="{{ $defaultDirections[$type] ?? '' }}">{{ $directionValue }}</textarea>
                                        <div class="td-hint">Printed under the test heading. Clearing the box restores the default wording.</div>
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </div>
                    <div class="td-foot">
                        <button type="button" class="td-btn td-btn-alt" data-close>Close</button>
                        <button type="submit" class="td-btn td-btn-ok">Save &amp; update preview</button>
                    </div>
                </dialog>

                <h3>General Instructions</h3>
                <div class="hint" style="margin-bottom:6px;">Printed under the student's details, above the first test. Clearing the box, or pressing Reset, restores the default wording.</div>
                <textarea id="generalInstructions" name="generalInstructions" rows="4" maxlength="600" aria-label="General instructions" data-default="{{ $defaultGeneralInstructions }}">{{ old('generalInstructions', $generalInstructions) }}</textarea>
                <div class="direction-actions">
                    <button type="button" class="direction-reset" id="generalInstructionsReset">Reset</button>
                </div>

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
                    <option value="minimal" {{ old('headerTemplate', $headerTpl) == 'minimal' ? 'selected' : '' }}>Minimal (left-aligned)</option>
                    <option value="marsu" {{ old('headerTemplate', $headerTpl) == 'marsu' ? 'selected' : '' }}>MarSU letterhead (logos, 4 lines, orange rule)</option>
                    <option value="coe" {{ old('headerTemplate', $headerTpl) == 'coe' ? 'selected' : '' }}>College of Engineering letterhead (logo, 6 lines, orange bar)</option>
                    <option value="accredited" {{ old('headerTemplate', $headerTpl) == 'accredited' ? 'selected' : '' }}>College of Engineering with accreditation (logos, accreditation strip, goal, "God bless" band)</option>
                </select>
                <div class="hint">Pre-designed layout — school name/department text only, no custom logo. The header and footer wording is fixed and cannot be edited.</div>

                <label for="headerTop">Header distance from top (px)</label>
                <input type="number" id="headerTop" name="headerTop" min="0" max="200" value="{{ old('headerTop', $headerTop) }}">
                <div class="hint">Moves the whole header down from the top edge of the paper. The MarSU letterhead matches the Word file at 30; the College of Engineering letterheads at 12.</div>

                <h3>Footer Template</h3>
                <select name="footerTemplate">
                    <option value="simple" {{ old('footerTemplate', $exam->footerTemplate) == 'simple' ? 'selected' : '' }}>Simple (centered, thin line)</option>
                    <option value="paged" {{ old('footerTemplate', $exam->footerTemplate) == 'paged' ? 'selected' : '' }}>Paged (left/right split, bold line)</option>
                    <option value="minimal" {{ old('footerTemplate', $exam->footerTemplate) == 'minimal' ? 'selected' : '' }}>Minimal (small, no line)</option>
                    <option value="marsu" {{ old('footerTemplate', $exam->footerTemplate) == 'marsu' ? 'selected' : '' }}>MarSU footer (orange rule, logos, vision/mission)</option>
                    <option value="coe" {{ old('footerTemplate', $exam->footerTemplate) == 'coe' ? 'selected' : '' }}>College of Engineering footer (quality award, ISO badge, vision/mission, tagline)</option>
                </select>

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

                <div class="save-bar"><button type="submit">Update Preview</button></div>
            
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
                    // Live preview: typing in a direction box updates that test on the paper right away.
                    // Nothing is saved until "Update preview" is pressed.
                    const boxes = document.querySelectorAll('textarea[data-type]');
                    function paperText(type) {
                        return Array.from(document.querySelectorAll('.part-direction[data-type]'))
                            .find(function (el) { return el.dataset.type === type; });
                    }
                    function sync(box) {
                        const text = box.value.trim() === '' ? box.dataset.default : box.value.trim();
                        // The paper preview (only present when the exact PDF preview is off).
                        const target = paperText(box.dataset.type);
                        if (target) target.querySelector('.direction-text').textContent = text;
                    }
                    boxes.forEach(function (box) { box.addEventListener('input', function () { sync(box); }); });

                    // Live preview for the general instructions (only present when the exact PDF preview is off).
                    (function () {
                        var gi = document.getElementById('generalInstructions');
                        if (!gi) return;
                        function showGi() {
                            var target = document.querySelector('.general-instructions .gi-text');
                            if (target) target.textContent = gi.value.trim() === '' ? gi.dataset.default : gi.value.trim();
                        }
                        gi.addEventListener('input', showGi);
                        document.getElementById('generalInstructionsReset').addEventListener('click', function () {
                            gi.value = gi.dataset.default;
                            showGi();
                        });
                    })();

                    // Live preview for answer-line length (inches). Empty = default, 0 = hidden.
                    document.querySelectorAll('input[data-answer-line]').forEach(function (input) {
                        input.addEventListener('input', function () {
                            var raw = input.value.trim();
                            var len = raw === '' ? parseFloat(input.dataset.default) : parseFloat(raw);
                            if (isNaN(len)) len = parseFloat(input.dataset.default);
                            len = Math.max(0, Math.min(4, len));
                            document.querySelectorAll('.answer-line[data-type]').forEach(function (el) {
                                if (el.dataset.type !== input.dataset.answerLine) return;
                                el.style.setProperty('--len', len + 'in');
                                el.classList.toggle('is-off', len === 0);
                            });
                        });
                    });
                    // "Customize" pop-up: one tab per question type, with a live one-line summary on each tab.
                    (function () {
                        var dlg = document.getElementById('type-dialog');
                        if (!dlg) return;
                        var tabs = Array.prototype.slice.call(dlg.querySelectorAll('[role="tab"]'));
                        var panels = Array.prototype.slice.call(dlg.querySelectorAll('[role="tabpanel"]'));

                        function pick(i, focus) {
                            tabs.forEach(function (t, k) {
                                var on = k === i;
                                t.setAttribute('aria-selected', on ? 'true' : 'false');
                                t.tabIndex = on ? 0 : -1;
                                panels[k].hidden = !on;
                            });
                            if (focus) tabs[i].focus();
                        }
                        function fmtIn(v) { return (Math.round(v * 10) / 10).toString(); }
                        function summarize(i) {
                            var panel = panels[i], parts = [];
                            var cols = panel.querySelector('input[type="radio"]:checked');
                            if (cols) parts.push(cols.value + (cols.value === '1' ? ' column' : ' columns'));
                            var line = panel.querySelector('input[data-answer-line]');
                            if (line) {
                                var raw = line.value.trim();
                                var len = raw === '' ? parseFloat(line.dataset.default) : parseFloat(raw);
                                if (isNaN(len)) len = parseFloat(line.dataset.default);
                                parts.push(len === 0 ? 'no answer line' : 'line ' + fmtIn(len) + ' in');
                            }
                            var dir = panel.querySelector('textarea');
                            var text = dir.value.trim();
                            var edited = text !== '' && text !== dir.dataset.default.trim();
                            if (edited) parts.push('edited direction');
                            tabs[i].querySelector('.td-tab-sum').textContent = parts.join(' \u00b7 ');
                            var badge = panel.querySelector('.td-badge');
                            badge.textContent = edited ? 'Edited' : 'Default';
                            badge.classList.toggle('is-edited', edited);
                        }

                        tabs.forEach(function (t, i) { t.addEventListener('click', function () { pick(i); }); });
                        dlg.querySelector('.td-tabs').addEventListener('keydown', function (e) {
                            var i = tabs.indexOf(document.activeElement), n = null;
                            if (i < 0) return;
                            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') n = (i + 1) % tabs.length;
                            else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') n = (i - 1 + tabs.length) % tabs.length;
                            else if (e.key === 'Home') n = 0;
                            else if (e.key === 'End') n = tabs.length - 1;
                            if (n !== null) { e.preventDefault(); pick(n, true); }
                        });
                        function refresh(e) {
                            var panel = e.target.closest('[role="tabpanel"]');
                            if (panel) summarize(panels.indexOf(panel));
                        }
                        dlg.addEventListener('input', refresh);
                        dlg.addEventListener('change', refresh);
                        dlg.addEventListener('click', function (e) {
                            if (e.target === dlg || e.target.closest('[data-close]')) dlg.close();
                        });
                        document.querySelectorAll('[data-open="type-dialog"]').forEach(function (btn) {
                            btn.addEventListener('click', function () { dlg.showModal(); });
                        });

                        panels.forEach(function (p, i) { summarize(i); });
                        pick(parseInt(dlg.dataset.start || '0', 10) || 0);
                        if (dlg.hasAttribute('data-autoopen')) dlg.showModal();   // a validation error inside: show it again
                    })();
                    document.querySelectorAll('.direction-reset').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            const box = document.getElementById(btn.dataset.target);
                            box.value = box.dataset.default;
                            sync(box);
                            box.dispatchEvent(new Event('input', { bubbles: true }));   // refresh the row's summary
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

            <a data-back href="{{ route('courses.exams.show', [$course, $exam]) }}" class="back-link">&larr; Back to Exam</a>
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
            <div class="paper {{ strtolower($exam->paperSize ?? 'letter') }} {{ $isLetterheadHdr ? 'has-marsu-header' : '' }} {{ $headerTpl === 'accredited' ? 'has-acc-header' : '' }} {{ $isLetterheadFtr ? 'has-marsu-footer' : '' }} {{ $footerTpl === 'coe' ? 'has-coe-footer' : '' }}" style="--header-top: {{ $headerTop }}px; --hdr-h: {{ match ($headerTpl) { 'coe' => '1.15in', 'accredited' => '2.3in', default => '1.0355in' } }}; --page-h: {{ ($exam->paperSize ?? 'Letter') === 'Legal' ? '14in' : '11in' }};">
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
                @if ($headerTpl === 'accredited')
                    <div class="acc-header">
                        <img class="ah-logo-l" src="{{ $src('images/exam-templates/marsu/seal.jpg') }}" alt="">
                        <div class="ah-text">
                            @foreach ($headerLines as $line)
                                <p @class(['ah-univ' => $loop->first, 'ah-college' => $loop->index === 1])>{{ $line }}</p>
                            @endforeach
                        </div>
                        <img class="ah-logo-r" src="{{ $src('images/exam-templates/marsu/college-logo.png') }}" alt="">
                        <div class="ah-accred">
                            <div class="ah-line"></div>
                            @foreach ($accAccreditations as [$label, $left])
                                <div class="ah-item" style="left: {{ $left }}in;"><span>{{ $label }}</span></div>
                            @endforeach
                        </div>
                        <div class="ah-band"></div>
                        <p class="ah-goal"><strong>GOAL:</strong> {{ $accGoal }}</p>
                        <p class="ah-discipline">{{ $accDiscipline }}</p>
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
                @if ($headerTpl === 'accredited')
                    @php
                        $accExamType = $tos->examType ?? '';
                        $accExamLine = $exam->isQuiz() ? 'Quiz' : trim($accExamType . ' Exam');
                        $accCourseLine = $exam->isQuiz() ? ($tos->title ?? '') : \App\Models\Tos::headingTitle($course);
                        $accTermLine = $exam->isQuiz() ? '' : trim(($tos->semester ?? '') . ' ' . ($tos->academicYear ?? ''));
                    @endphp
                    <div class="acc-title">
                        <div class="at-exam">{{ $accExamLine }}</div>
                        @if ($accCourseLine !== '')<div class="at-course">{{ $accCourseLine }}</div>@endif
                        @if ($accTermLine !== '')<div class="at-term">{{ $accTermLine }}</div>@endif
                        <div class="at-blessing" aria-hidden="true">
                            @for ($b = 0; $b < 11; $b++)<span>GOD BLESS!</span>@endfor
                        </div>
                    </div>
                @elseif ($isLetterheadHdr)
                    <div class="marsu-title">{{ $tos->title ?? 'Exam' }} — {{ $tos->examType ?? '' }}</div>
                @else
                    @php $headerClass = 'header-' . $headerTpl; @endphp
                    <div class="{{ $headerClass }}">
                        <img src="{{ $src($headerImageMap[$headerTpl] ?? $headerImageMap['minimal']) }}">
                        <div class="school">{{ $exam->schoolName ?? 'School Name' }}</div>
                        <div class="dept">{{ $exam->department ?? 'Department' }}</div>
                        <div class="title">{{ $tos->title ?? 'Exam' }} — {{ $tos->examType ?? '' }}</div>
                    </div>
                @endif

                <div class="info-row {{ $headerTpl === 'accredited' ? 'info-row-boxed' : '' }}">
                    <div class="info-field"><span>Name:</span><span class="info-line"></span></div>
                    @if ($headerTpl === 'accredited')
                        <div class="info-score">
                            <span>Score:</span>
                            <div class="score-box" aria-label="Score out of {{ \App\Support\Points::number($exam->totalScore) }}">
                                <svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true"><line x1="0" y1="100" x2="100" y2="0" stroke="#000" stroke-width="1.5" vector-effect="non-scaling-stroke"/></svg>
                                <span class="score-total">{{ \App\Support\Points::number($exam->totalScore) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="info-field info-half"><span>Score:</span><span class="info-line"></span></div>
                    @endif
                    <div class="info-field"><span>{{ $headerTpl === 'accredited' ? 'Year & Section:' : 'Section:' }}</span><span class="info-line"></span></div>
                    <div class="info-field info-half"><span>Date:</span><span class="info-line"></span></div>
                </div>

                <div class="general-instructions"><strong>General Instructions:</strong> <span class="gi-text">{{ $generalInstructions }}</span></div>

               @php $partNum = 1; $romanParts = ['I','II','III','IV','V','VI']; $letters = ['A','B','C','D','E','F','G','H','I','J','K','L']; @endphp
                @foreach ($questions as $type => $group)
                    @php $itemCounter = 1; @endphp
                    <div class="part-top">
                        <div class="part-header">
                            <span class="ph-num">Test {{ $romanParts[$partNum - 1] ?? $partNum }}.</span>
                            <span class="ph-type">{{ $type }} (Items 1–{{ $group->count() }})</span>
                        </div>
                        <div class="part-direction" data-type="{{ $type }}">Direction: <span class="direction-text">{{ $directions[$type] ?? '' }}</span></div>
                    </div>

                   @php
                        $alLen = in_array($type, $answerLineTypes, true) ? ($answerLines[$type] ?? $answerLineDefault) : null;
                        $alStyle = $alLen === null ? '' : '--len: ' . rtrim(rtrim(number_format($alLen, 1, '.', ''), '0'), '.') . 'in;';
                        $alClass = ($alLen !== null && $alLen <= 0) ? 'answer-line is-off' : 'answer-line';
                   @endphp
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
                                    <div class="item q-row"><span class="q-lead"><span class="{{ $alClass }}" data-type="{{ $type }}" style="{{ $alStyle }}"></span><span class="q-num">{{ $itemCounter }}.</span></span><span class="q-text">{{ $question->content }}</span></div>
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
                        @php
                            $sectionCols = $columnsPerPart[$type] ?? 1;
                            // Usable width of one column in px: page text width (8.5in minus 50px side padding, 96px/in)
                            // less the 30px column gaps and the 15px choice indent. Choices go side by side only when the
                            // estimated width of all of them (about 6.4px per character at 12px, plus the 28px gaps) fits.
                            $colWidthPx = ((8.5 * 96 - 100) - 30 * ($sectionCols - 1)) / $sectionCols - 15;
                            $choicesFit = function ($choices) use ($colWidthPx, $letters) {
                                $need = 0;
                                foreach ($choices as $i => $c) {
                                    $need += mb_strlen(($letters[$i] ?? '') . '. ' . trim($c)) * 6.4;
                                }
                                $need += 28 * max(0, count($choices) - 1);
                                return $need <= $colWidthPx;
                            };
                        @endphp
                        <div class="question-list" style="--cols: {{ $sectionCols }};">
                            @foreach ($group as $question)
                                <div class="question-item">
                                    <div class="q-row"><span class="q-lead">@if ($alLen !== null)<span class="{{ $alClass }}" data-type="{{ $type }}" style="{{ $alStyle }}"></span>@endif<span class="q-num">{{ $itemCounter }}.</span></span><span class="q-text">{{ $question->content }}</span></div>
                                    @if ($question->choices)
                                        @php $choiceList = explode(',', $question->choices); @endphp
                                        <div class="choices {{ $choicesFit($choiceList) ? 'choices-row' : '' }}">
                                            @foreach ($choiceList as $i => $choice)
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
        // Engineering 12 px) unless the distance was already changed by hand.
        (function () {
            var hdr = document.querySelector('select[name="headerTemplate"]');
            var top = document.getElementById('headerTop');
            if (hdr) hdr.addEventListener('change', function () {
                if (top && (top.value === '50' || top.value === '30' || top.value === '12')) {
                    if (hdr.value === 'marsu') top.value = '30';
                    if (hdr.value === 'coe' || hdr.value === 'accredited') top.value = '12';
                }
            });
        })();
    </script>
    @endunless
</body>
</html>