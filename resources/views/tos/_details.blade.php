@php
    $t = $tos ?? null;
    $hoursValue = old('hoursPerWeek', isset($t->hoursPerWeek) ? rtrim(rtrim(number_format((float) $t->hoursPerWeek, 1, '.', ''), '0'), '.') : '');
@endphp
<section class="work-section" aria-labelledby="tf-details">
    <div class="work-section-head">
        <span class="work-section-num" aria-hidden="true">01</span>
        <div><h2 id="tf-details">Details</h2><p>The heading (subject code/title, credit units) is filled in automatically from the course. Set the exam, academic year, semester and hours.</p></div>
    </div>
    <div class="work-section-body">
        <div class="work-field-row">
            <div class="work-field">
                <label class="work-label" for="examType">Exam</label>
                <select name="examType" id="examType" class="work-select {{ $errors->has('examType') ? 'is-invalid' : '' }}" required>
                    <option value="">-- Select --</option>
                    @foreach ($examTypes as $type)
                        <option value="{{ $type }}" {{ old('examType', $t->examType ?? null) == $type ? 'selected' : '' }}>{{ $type }} Examination</option>
                    @endforeach
                </select>
            </div>
            <div class="work-field">
                <label class="work-label" for="academicYear">Academic year</label>
                <input type="text" id="academicYear" name="academicYear" class="work-input {{ $errors->has('academicYear') ? 'is-invalid' : '' }}" value="{{ old('academicYear', $t->academicYear ?? '') }}" placeholder="2025-2026" pattern="\d{4}-\d{4}" maxlength="9" required @if ($errors->has('academicYear')) aria-invalid="true" @endif>
            </div>
        </div>
        <div class="work-field-row">
            <div class="work-field">
                <label class="work-label" for="semester">Semester</label>
                <select name="semester" id="semester" class="work-select {{ $errors->has('semester') ? 'is-invalid' : '' }}" required>
                    <option value="">-- Select --</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}" {{ old('semester', $t->semester ?? null) == $sem ? 'selected' : '' }}>{{ $sem }}</option>
                    @endforeach
                </select>
            </div>
            <div class="work-field">
                <label class="work-label" for="hoursPerWeek">Hours (per week)</label>
                <input type="number" id="hoursPerWeek" name="hoursPerWeek" class="work-input {{ $errors->has('hoursPerWeek') ? 'is-invalid' : '' }}" value="{{ $hoursValue }}" step="0.5" min="0.5" max="99" required @if ($errors->has('hoursPerWeek')) aria-invalid="true" @endif>
            </div>
        </div>
        <p class="work-hint" style="margin:6px 0 0;">Heading preview: <strong>{{ \App\Models\Tos::headingTitle($course) }}</strong>@if ($course->creditUnits !== null) · {{ \App\Support\TosHeading::units($course->creditUnits) }}@endif</p>
    </div>
</section>
