{{-- Live "questions available in the Question Bank" indicator for the ToS builder (filled in by public/js/tos-form.js).
     Expects $topics (the course's topics) and $bank ([topicId][“Bloom|Type”] => count). --}}
@php
    $bankPayload = [
        'topics' => collect($topics)->mapWithKeys(fn ($t) => [(string) $t->topicId => $t->topicName])->all(),
        'counts' => collect($bank)->map(fn ($cells) => (object) $cells)->all(),
    ];
@endphp
<div class="work-bank" id="bankIndicator" role="status" aria-live="polite" aria-label="Questions available in the question bank" data-bank="{{ json_encode($bankPayload) }}">
    <div class="work-bank-head">
        <span class="work-score-label">Question bank</span>
        <span class="work-bank-for" id="bankFor"></span>
    </div>
    <div class="work-bank-body">
        <strong class="work-bank-count" id="bankAvailable">0</strong>
        <div>
            <p class="work-bank-main" id="bankMain">Choose a Bloom level and question type to see how many questions are available.</p>
            <p class="work-bank-sub" id="bankType"></p>
            <ul class="work-bank-topics" id="bankTopics"></ul>
        </div>
    </div>
</div>
