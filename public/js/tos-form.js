/* DESBank ToS form: Table 2 (Bloom distribution) builder, shared by create and edit.
   Hidden-input contract with TosController is unchanged:
   bloomKey[] = "Level|Type", pointsPerItem[key], itemCount[key]. */
(function () {
    'use strict';

    var table = document.getElementById('bloomRowsTable');
    if (!table) return;

    var form = table.closest('form');
    var body = document.getElementById('bloomRowsBody');
    var totalEl = document.getElementById('bloomRunningTotal');
    var statusEl = document.getElementById('bloomTotalStatus');
    var errorEl = document.getElementById('bloomRowError');
    var addBtn = document.getElementById('addBloomRowBtn');
    var targetInput = form.querySelector('input[name="totalScore"]');
    var COLS = 6;

    var accEl = document.getElementById('scoreAccumulated');
    var tgtEl = document.getElementById('scoreTarget');
    var remEl = document.getElementById('scoreRemaining');
    var remNoteEl = document.getElementById('scoreRemainingNote');
    var itemsEl = document.getElementById('scoreItems');
    var barEl = document.getElementById('scoreBar');
    var barFillEl = document.getElementById('scoreBarFill');
    var summaryEl = document.getElementById('scoreSummary');

    var rows = [];
    try { rows = JSON.parse(table.getAttribute('data-initial') || '[]'); } catch (e) { rows = []; }
    rows = rows.map(function (r) {
        return { level: String(r.level), type: String(r.type), points: Number(r.points) || 0, count: parseInt(r.count, 10) || 0 };
    });

    var touchedKey = null;

    function td(content, cls) {
        var cell = document.createElement('td');
        if (cls) cell.className = cls;
        if (content instanceof Node) cell.appendChild(content); else cell.textContent = content;
        return cell;
    }

    function chip(text, extra) {
        var s = document.createElement('span');
        s.className = 'work-chip' + (extra ? ' ' + extra : '');
        s.textContent = text;
        return s;
    }

    // Accumulated total, target, remaining and item count, kept in sync on every change.
    function updateSummary(total, target) {
        if (!summaryEl) return;
        var items = rows.reduce(function (n, r) { return n + r.count; }, 0);
        var hasTarget = !isNaN(target) && target > 0;
        var diff = hasTarget ? target - total : NaN;
        var state = !hasTarget ? '' : (Math.abs(diff) < 0.005 ? 'is-ok' : (diff < 0 ? 'is-over' : 'is-short'));

        accEl.textContent = total.toFixed(2);
        itemsEl.textContent = String(items);
        tgtEl.textContent = hasTarget ? target.toFixed(2) : '\u2014';
        if (!hasTarget) {
            remEl.textContent = '\u2014';
            remNoteEl.textContent = 'enter a target';
        } else if (state === 'is-ok') {
            remEl.textContent = '0.00';
            remNoteEl.textContent = 'target reached';
        } else if (diff > 0) {
            remEl.textContent = diff.toFixed(2);
            remNoteEl.textContent = 'still to add';
        } else {
            remEl.textContent = Math.abs(diff).toFixed(2);
            remNoteEl.textContent = 'over target';
        }
        var pct = hasTarget ? Math.max(0, Math.min(100, (total / target) * 100)) : 0;
        barFillEl.style.width = pct.toFixed(1) + '%';
        barEl.setAttribute('aria-valuenow', String(Math.round(pct)));
        summaryEl.className = 'work-score-summary' + (state ? ' ' + state : '');
    }

    function updateStatus(total) {
        var target = targetInput ? parseFloat(targetInput.value) : NaN;
        updateSummary(total, target);
        var cls = 'work-status', msg;
        if (!rows.length) {
            msg = 'Add at least one row';
        } else if (isNaN(target)) {
            msg = 'Enter a total score to compare';
        } else if (Math.abs(total - target) < 0.005) {
            cls += ' is-ok'; msg = 'Matches total score (' + target.toFixed(2) + ')';
        } else if (total < target) {
            cls += ' is-bad'; msg = 'Short by ' + (target - total).toFixed(2) + ' of ' + target.toFixed(2);
        } else {
            cls += ' is-bad'; msg = 'Over by ' + (total - target).toFixed(2) + ' of ' + target.toFixed(2);
        }
        statusEl.className = cls;
        statusEl.textContent = msg;
    }

    function render() {
        body.textContent = '';
        var total = 0;

        if (!rows.length) {
            var empty = document.createElement('tr');
            empty.className = 'work-table-empty';
            var cell = td('No rows yet. Choose a level and type above, then add it.');
            cell.colSpan = COLS;
            empty.appendChild(cell);
            body.appendChild(empty);
        }

        rows.forEach(function (row, i) {
            var score = row.points * row.count;
            total += score;
            var tr = document.createElement('tr');
            if (touchedKey === row.level + '|' + row.type) tr.className = 'is-new';

            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'removeBloomRow work-row-link work-row-delete';
            remove.setAttribute('data-i', i);
            remove.setAttribute('aria-label', 'Remove ' + row.level + ', ' + row.type);
            remove.textContent = 'Remove';

            tr.appendChild(td(chip(row.level)));
            tr.appendChild(td(chip(row.type, 'work-chip-gold')));
            tr.appendChild(td(String(row.points), 'work-num'));
            tr.appendChild(td(String(row.count), 'work-num'));
            tr.appendChild(td(score.toFixed(2), 'work-num'));
            tr.appendChild(td(remove));
            body.appendChild(tr);
        });

        touchedKey = null;
        totalEl.textContent = total.toFixed(2);
        updateStatus(total);
    }

    function currentTotal() {
        return rows.reduce(function (sum, r) { return sum + r.points * r.count; }, 0);
    }

    function showError(message) {
        errorEl.textContent = message;
        errorEl.hidden = false;
    }

    function addRow() {
        var level = document.getElementById('newBloomLevel').value;
        var type = document.getElementById('newQuestionType').value;
        var points = parseFloat(document.getElementById('newPoints').value) || 0;
        var count = parseInt(document.getElementById('newCount').value, 10) || 0;

        if (points <= 0 || count <= 0) {
            showError('Points per item and item count must both be greater than zero.');
            return;
        }
        errorEl.hidden = true;

        var existingIndex = rows.findIndex(function (r) { return r.level === level && r.type === type; });
        if (existingIndex >= 0) {
            rows[existingIndex] = { level: level, type: type, points: points, count: count };
        } else {
            rows.push({ level: level, type: type, points: points, count: count });
        }
        touchedKey = level + '|' + type;
        render();
    }

    addBtn.addEventListener('click', addRow);

    // Enter inside the row builder adds the row instead of submitting the whole form.
    ['newPoints', 'newCount'].forEach(function (id) {
        document.getElementById(id).addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); addRow(); }
        });
    });

    body.addEventListener('click', function (e) {
        var btn = e.target.closest('.removeBloomRow');
        if (!btn) return;
        var i = parseInt(btn.getAttribute('data-i'), 10);
        rows.splice(i, 1);
        render();
        // Keep keyboard focus inside the table instead of dropping it to the page.
        var remaining = body.querySelectorAll('.removeBloomRow');
        (remaining.length ? remaining[Math.min(i, remaining.length - 1)] : addBtn).focus();
    });

    if (targetInput) {
        targetInput.addEventListener('input', function () { updateStatus(currentTotal()); });
    }

    form.addEventListener('submit', function () {
        var container = document.getElementById('bloomHiddenInputs');
        container.textContent = '';
        rows.forEach(function (row) {
            var key = row.level + '|' + row.type;
            [['bloomKey[]', key], ['pointsPerItem[' + key + ']', row.points], ['itemCount[' + key + ']', row.count]].forEach(function (pair) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = pair[0];
                input.value = pair[1];
                container.appendChild(input);
            });
        });
    });

    render();
})();
