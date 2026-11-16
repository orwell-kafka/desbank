/* DESBank question form: Multiple Choice answer choices as separate inputs.
   Each choice is submitted as choices[]; QuestionController joins them with commas,
   so stored data, exam printing and the question bank list are unchanged. */
(function () {
    'use strict';

    var list = document.getElementById('choiceList');
    var addBtn = document.getElementById('addChoiceBtn');
    if (!list || !addBtn) return;

    var field = document.getElementById('choicesField');
    var form = list.closest('form');
    var errorEl = document.getElementById('choiceError');
    var answerSelect = document.getElementById('answerChoiceSelect');   // Multiple Choice: correct answer picker
    var answerInput = document.getElementById('answerInput');            // the field that is actually submitted
    var answerError = document.getElementById('answerChoiceError');
    var chosenRow = null;       // the choice row currently picked as the correct answer
    var optionRows = [];        // choice rows behind the picker's options (in order)
    var LETTERS = 'ABCDEFGHIJKL';
    var MAX = LETTERS.length;   // the printed exam labels choices A-L
    var MIN_ROWS = 2;

    var initial = [];
    try { initial = JSON.parse(list.getAttribute('data-initial') || '[]'); } catch (e) { initial = []; }
    initial = initial.map(function (v) { return String(v == null ? '' : v); });
    while (initial.length < 4) initial.push('');   // start with a typical A-D set

    function rows() { return Array.prototype.slice.call(list.children); }
    function inputOf(row) { return row.querySelector('input'); }

    // Fill the correct-answer picker with the choices typed so far (blank boxes are skipped).
    function rebuildAnswerOptions() {
        if (!answerSelect) return;
        answerSelect.innerHTML = '';
        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = '-- Select --';
        placeholder.disabled = true;
        placeholder.hidden = true;
        answerSelect.appendChild(placeholder);

        optionRows = [];
        var picked = null;
        rows().forEach(function (row, i) {
            var value = inputOf(row).value.trim();
            if (!value) return;
            var opt = document.createElement('option');
            opt.value = value;
            opt.textContent = LETTERS.charAt(i) + '. ' + value;
            answerSelect.appendChild(opt);
            optionRows.push(row);
            if (row === chosenRow) picked = opt;
        });
        if (picked) { picked.selected = true; }
        else { placeholder.selected = true; chosenRow = null; }
    }

    // Copy the picked choice into the submitted correctAnswer field.
    function syncAnswer() {
        if (answerSelect && answerInput) answerInput.value = answerSelect.value;
    }

    function renumber() {
        var all = rows();
        all.forEach(function (row, i) {
            row.querySelector('.work-choice-letter').textContent = LETTERS.charAt(i) + '.';
            inputOf(row).setAttribute('aria-label', 'Answer choice ' + LETTERS.charAt(i));
            var rm = row.querySelector('.work-choice-remove');
            rm.setAttribute('aria-label', 'Remove choice ' + LETTERS.charAt(i));
            rm.disabled = all.length <= MIN_ROWS;
        });
        addBtn.disabled = all.length >= MAX;
        addBtn.textContent = all.length >= MAX ? 'Maximum of ' + MAX + ' choices' : '+ Add answer';
        rebuildAnswerOptions();
    }

    function makeRow(value) {
        var li = document.createElement('li');
        li.className = 'work-choice-row';

        var letter = document.createElement('span');
        letter.className = 'work-choice-letter';
        letter.setAttribute('aria-hidden', 'true');

        var input = document.createElement('input');
        input.type = 'text';
        input.name = 'choices[]';
        input.className = 'work-input';
        input.maxLength = 255;
        input.autocomplete = 'off';
        input.value = value || '';

        var rm = document.createElement('button');
        rm.type = 'button';
        rm.className = 'work-row-btn work-row-btn-bad work-choice-remove';
        rm.textContent = 'Remove';

        li.appendChild(letter);
        li.appendChild(input);
        li.appendChild(rm);
        return li;
    }

    function addRow(value, afterRow) {
        if (rows().length >= MAX) return null;
        var row = makeRow(value);
        if (afterRow && afterRow.nextSibling) list.insertBefore(row, afterRow.nextSibling);
        else list.appendChild(row);
        return row;
    }

    function clearError() {
        if (!errorEl) return;
        errorEl.hidden = true;
        errorEl.textContent = '';
    }

    initial.forEach(function (v) { addRow(v); });
    if (answerSelect) {
        var saved = (answerSelect.getAttribute('data-correct') || '').trim();
        if (saved) {
            chosenRow = rows().filter(function (r) { return inputOf(r).value.trim() === saved; })[0] || null;
        }
    }
    renumber();

    if (answerSelect) {
        answerSelect.addEventListener('change', function () {
            chosenRow = optionRows[answerSelect.selectedIndex - 1] || null;
            syncAnswer();
            if (answerError) { answerError.hidden = true; answerError.textContent = ''; }
        });
    }

    addBtn.addEventListener('click', function () {
        var row = addRow('');
        renumber();
        clearError();
        if (row) inputOf(row).focus();
    });

    list.addEventListener('click', function (e) {
        var btn = e.target.closest('.work-choice-remove');
        if (!btn || btn.disabled) return;
        var row = btn.closest('li');
        var all = rows();
        var i = all.indexOf(row);
        list.removeChild(row);
        if (row === chosenRow) chosenRow = null;
        renumber();
        syncAnswer();
        var left = rows();
        // Keep keyboard focus in the list after removing.
        inputOf(left[Math.min(i, left.length - 1)]).focus();
    });

    // Enter adds the next choice instead of submitting the whole form.
    list.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' || !e.target.matches('input')) return;
        e.preventDefault();
        var row = addRow('', e.target.closest('li'));
        renumber();
        if (row) inputOf(row).focus();
    });

    // Typing or pasting "A, B, C" into one box splits it into separate choices.
    list.addEventListener('input', function (e) {
        var input = e.target;
        if (!input.matches('input')) return;
        clearError();
        if (input.value.indexOf(',') === -1) { rebuildAnswerOptions(); syncAnswer(); return; }
        var parts = input.value.split(',').map(function (p) { return p.trim(); });
        var current = input.closest('li');
        input.value = parts[0];
        var last = current;
        for (var i = 1; i < parts.length; i++) {
            if (!parts[i]) continue;
            var row = addRow(parts[i], last);
            if (!row) break;
            last = row;
        }
        if (input.value.indexOf(',') !== -1) input.value = input.value.replace(/,/g, ' ');
        renumber();
        syncAnswer();
        inputOf(last).focus();
    });

    // Multiple Choice needs at least two filled-in choices. Runs before workspace.js's
    // pending-state handler so a blocked submit never leaves the button stuck on "Saving…".
    form.addEventListener('submit', function (e) {
        var visible = field.style.display !== 'none';
        if (!visible) return;
        var filled = rows().filter(function (r) { return inputOf(r).value.trim() !== ''; });
        if (filled.length >= 2) {
            if (!answerSelect) return;
            syncAnswer();
            if (answerSelect.value !== '') return;
            e.preventDefault();
            e.stopImmediatePropagation();
            if (answerError) {
                answerError.textContent = 'Pick the correct answer from your choices.';
                answerError.hidden = false;
            }
            answerSelect.focus();
            return;
        }
        e.preventDefault();
        e.stopImmediatePropagation();
        if (errorEl) {
            errorEl.textContent = 'Add at least two answer choices.';
            errorEl.hidden = false;
        }
        var firstEmpty = rows().filter(function (r) { return inputOf(r).value.trim() === ''; })[0];
        inputOf(firstEmpty || rows()[0]).focus();
    }, true);
})();
