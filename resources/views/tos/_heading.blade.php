@php
    $isMidterm = $tos->examType === 'Midterm';
    $isFinal = $tos->examType === 'Final';
@endphp
<style>
    .tos-heading { font-family: 'Times New Roman', 'Liberation Serif', Times, serif; font-size: 16px; color: #000; background: #fff; border: 1px solid #d9dee3; padding: 18px 22px; margin: 0 0 24px; display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(0, 1fr); column-gap: 36px; row-gap: 4px; }
    .tos-heading .th-cell { display: flex; align-items: baseline; gap: 6px; min-width: 0; }
    .tos-heading .th-label { white-space: nowrap; }
    .tos-heading .th-value { flex: 1; min-width: 0; border-bottom: 1px solid #000; padding: 0 6px; text-align: center; min-height: 1.25em; }
    .tos-heading .th-wide { grid-column: 1; }
    .tos-heading sup { font-size: 0.65em; }
    @media (max-width: 640px) { .tos-heading { grid-template-columns: 1fr; } }
    @media print { .tos-heading { border: 0; padding: 0; } }
</style>
<div class="tos-heading" role="group" aria-label="ToS heading">
    <div class="th-cell"><span class="th-label">Subject Code/ Title:</span><span class="th-value">{{ \App\Models\Tos::headingTitle($course) }}</span></div>
    <div class="th-cell"><span class="th-label">Hours:</span><span class="th-value">{{ \App\Support\TosHeading::hours($tos->hoursPerWeek) }}</span></div>

    <div class="th-cell"><span class="th-label">Credit Units:</span><span class="th-value">{{ \App\Support\TosHeading::units($course->creditUnits) }}</span></div>
    <div></div>

    <div class="th-cell"><span class="th-label">Academic Year:</span><span class="th-value">{{ $tos->academicYear }}</span></div>
    <div class="th-cell"><span class="th-label">Semester:</span><span class="th-value">{{ $tos->semester }}</span></div>

    <div class="th-cell"><span class="th-label">Midterm Examination:</span><span class="th-value">{{ $isMidterm ? '✓' : '' }}</span></div>
    <div class="th-cell"><span class="th-label">Final Examination:</span><span class="th-value">{{ $isFinal ? '✓' : '' }}</span></div>
</div>
