/* DESBank question form: insert blanks / answer lines with a button instead of typing underscores.
   Works with the plain-text contract: a blank is a run of underscores inside the question text,
   which the exam views already print as-is. */
(function () {
    'use strict';

    var area = document.getElementById('content');
    var tools = document.getElementById('blankTools');
    if (!area || !tools) return;

    var lengthSelect = document.getElementById('blankLength');
    var summary = document.getElementById('blankSummary');
    var typeSelect = document.getElementById('questionType');
    var answerInput = document.getElementById('answerInput');

    function blank() { return new Array((parseInt(lengthSelect.value, 10) || 10) + 1).join('_'); }

    // execCommand keeps the browser's undo stack; setRangeText is the fallback.
    function replaceRange(start, end, text) {
        area.focus();
        area.setSelectionRange(start, end);
        var done = false;
        try { done = document.execCommand('insertText', false, text); } catch (e) { done = false; }
        if (!done || area.value.substr(start, text.length) !== text) {
            area.setRangeText(text, start, end, 'end');
        }
        area.setSelectionRange(start + text.length, start + text.length);
        area.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function insertAtCursor() {
        var start = area.selectionStart, end = area.selectionEnd;
        var v = area.value;
        var selected = v.slice(start, end).trim();
        var before = start > 0 ? v.charAt(start - 1) : '';
        var after = end < v.length ? v.charAt(end) : '';
        var text = (before && !/[\s(\[“"']/.test(before) ? ' ' : '') + blank() + (after && !/[\s.,;:!?)\]”"']/.test(after) ? ' ' : '');
        replaceRange(start, end, text);
        // Highlighting the answer word and pressing the button also records it as the answer.
        if (selected && typeSelect && typeSelect.value === 'Fill in the Blanks' && answerInput && !answerInput.value.trim()) {
            answerInput.value = selected;
        }
    }

    function atStart() {
        if (/^\s*_{3,}/.test(area.value)) return;
        replaceRange(0, 0, blank() + ' ');
    }

    function atEnd() {
        var len = area.value.length;
        replaceRange(len, len, (len && !/\s$/.test(area.value) ? ' ' : '') + blank());
    }

    function update() {
        var count = (area.value.match(/_{3,}/g) || []).length;
        var isFill = typeSelect && typeSelect.value === 'Fill in the Blanks';
        var msg = count === 1 ? '1 blank in this question.' : count + ' blanks in this question.';
        if (isFill && count === 0) msg = 'No blank yet. Click where it should go, then use \u201cInsert blank at cursor\u201d.';
        summary.textContent = msg;
    }

    tools.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-blank]');
        if (!btn) return;
        var action = btn.getAttribute('data-blank');
        if (action === 'cursor') insertAtCursor();
        else if (action === 'start') atStart();
        else if (action === 'end') atEnd();
        else if (action === 'clear') {
            area.value = area.value.replace(/\s*_{3,}\s*/g, ' ').replace(/ {2,}/g, ' ').trim();
            area.dispatchEvent(new Event('input', { bubbles: true }));
            area.focus();
        }
    });

    area.addEventListener('input', update);
    if (typeSelect) typeSelect.addEventListener('change', update);
    update();
})();
