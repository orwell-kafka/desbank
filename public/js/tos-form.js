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
    var COLS = 6;

    var accEl = document.getElementById('scoreAccumulated');
    var itemsEl = document.getElementById('scoreItems');
    var summaryEl = document.getElementById('scoreSummary');

    var rows = [];
    try { rows = JSON.parse(table.getAttribute('data-initial') || '[]'); } catch (e) { rows = []; }
    rows = rows.map(function (r) {
        return { level: String(r.level), type: String(r.type), points: Number(r.points) || 0, count: parseInt(r.count, 10) || 0 };
    });

    var touchedKey = null;

    // Points are shown without trailing zeroes and with the word "Points": 20 -> "20 Points",
    // 2.5 -> "2.5 Points", 1 -> "1 Point". Keep in step with App\Support\Points in PHP.
    function fmtNumber(n) {
        var t = (Math.round((Number(n) || 0) * 100) / 100).toFixed(2).replace(/\.?0+$/, '');
        return (t === '' || t === '-0') ? '0' : t;
    }
    function fmtPoints(n) {
        return fmtNumber(n) + (Math.abs((Number(n) || 0) - 1) < 0.00001 ? ' Point' : ' Points');
    }

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

    // The total score is never typed in: it is the sum of the Table 2 rows and is saved that way.
    // It is recalculated on every add, remove and change, together with the item count.
    function updateSummary(total) {
        if (!summaryEl) return;
        var items = rows.reduce(function (n, r) { return n + r.count; }, 0);
        accEl.textContent = fmtPoints(total);
        itemsEl.textContent = String(items);
        summaryEl.className = 'work-score-summary' + (rows.length ? ' is-ok' : '');
    }

    function updateStatus(total) {
        updateSummary(total);
        var cls = 'work-status', msg;
        if (!rows.length) {
            msg = 'Add at least one row';
        } else {
            cls += ' is-ok';
            msg = 'Total score: ' + fmtPoints(total) + ' (' + rows.length + (rows.length === 1 ? ' row' : ' rows') + ')';
        }
        statusEl.className = cls;
        statusEl.textContent = msg;
    }

    // ---- Question bank availability -------------------------------------------------------------
    // Shows how many questions the bank holds for the selected Bloom level / question type, counting
    // only the topics ticked in Table 1 (all topics while none is ticked), so teachers know how many
    // items they can plan before generating the exam.
    var bankEl = document.getElementById('bankIndicator');
    var bank = { topics: {}, counts: {} };
    if (bankEl) {
        try { bank = JSON.parse(bankEl.getAttribute('data-bank') || '{}'); } catch (e) { /* keep empty */ }
        bank.topics = bank.topics || {};
        bank.counts = bank.counts || {};
    }
    var levelSel = document.getElementById('newBloomLevel');
    var typeSel = document.getElementById('newQuestionType');
    var countInput = document.getElementById('newCount');

    function selectedTopicIds() {
        var ids = Array.prototype.map.call(form.querySelectorAll('input[name="topicId[]"]:checked'), function (c) { return c.value; });
        return ids.length ? ids : Object.keys(bank.topics);
    }

    function bankFor(level, type, topicIds) {
        var key = level + '|' + type;
        return topicIds.reduce(function (n, id) { return n + ((bank.counts[id] && bank.counts[id][key]) || 0); }, 0);
    }

    function plural(n, word) { return n + ' ' + word + (n === 1 ? '' : 's'); }

    function updateBank() {
        if (!bankEl || !levelSel || !typeSel) return;
        var level = levelSel.value, type = typeSel.value;
        var ticked = form.querySelectorAll('input[name="topicId[]"]:checked').length;
        var ids = selectedTopicIds();
        var available = bankFor(level, type, ids);
        var wanted = parseInt(countInput.value, 10) || 0;
        var planned = 0;
        rows.forEach(function (r) { if (r.level === level && r.type === type) planned = r.count; });

        var typeTotal = 0;
        ids.forEach(function (id) {
            Object.keys(bank.counts[id] || {}).forEach(function (k) {
                if (k.split('|')[1] === type) typeTotal += bank.counts[id][k];
            });
        });

        document.getElementById('bankFor').textContent = level + ' / ' + type;
        document.getElementById('bankAvailable').textContent = String(available);

        var main = document.getElementById('bankMain');
        var state = '';
        if (available === 0) {
            state = 'is-none';
            main.textContent = 'No ' + level + ' / ' + type + ' questions in ' + (ticked ? 'the selected topics' : 'this course') + ' yet. Add some to the question bank before generating an exam.';
        } else if (wanted > available) {
            state = 'is-over';
            main.textContent = plural(available, 'question') + ' available, but you are asking for ' + wanted + '. You can add at most ' + available + ' for this row.';
        } else {
            state = 'is-ok';
            main.textContent = plural(available, 'question') + ' available in ' + (ticked ? 'the selected topics' : 'all topics') + ', so you can add up to ' + available + ' item' + (available === 1 ? '' : 's') + ' for this row.';
        }
        if (planned > 0) {
            main.textContent += ' This row currently plans ' + planned + '.';
        }
        bankEl.className = 'work-bank' + (state ? ' ' + state : '');

        document.getElementById('bankType').textContent = type + ' in total (all Bloom levels): ' + plural(typeTotal, 'question') + '.';

        var list = document.getElementById('bankTopics');
        list.textContent = '';
        ids.forEach(function (id) {
            var n = bankFor(level, type, [id]);
            var li = document.createElement('li');
            li.className = n === 0 ? 'is-empty' : '';
            var name = document.createElement('span');
            name.textContent = bank.topics[id] || 'Topic';
            var num = document.createElement('b');
            num.textContent = String(n);
            li.appendChild(name);
            li.appendChild(num);
            list.appendChild(li);
        });
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
            tr.appendChild(td(fmtPoints(row.points), 'work-num'));
            var countCell = td(String(row.count), 'work-num');
            if (bankEl) {
                var inBank = bankFor(row.level, row.type, selectedTopicIds());
                if (row.count > inBank) {
                    var warn = document.createElement('small');
                    warn.className = 'work-bank-warn';
                    warn.textContent = 'only ' + inBank + ' in bank';
                    countCell.appendChild(document.createElement('br'));
                    countCell.appendChild(warn);
                }
            }
            tr.appendChild(countCell);
            tr.appendChild(td(fmtPoints(score), 'work-num'));
            tr.appendChild(td(remove));
            body.appendChild(tr);
        });

        touchedKey = null;
        totalEl.textContent = fmtPoints(total);
        updateStatus(total);
        updateBank();
        updatePending();
    }

    // Live preview of the row being typed, so the effect on the total is visible before pressing
    // "Add row", not only after every row has been entered.
    function updatePending() {
        var el = document.getElementById('pendingPreview');
        if (!el) return;
        var level = document.getElementById('newBloomLevel').value;
        var type = document.getElementById('newQuestionType').value;
        var points = parseFloat(document.getElementById('newPoints').value) || 0;
        var count = parseInt(document.getElementById('newCount').value, 10) || 0;
        if (points <= 0 || count <= 0) { el.textContent = ''; return; }

        var rowScore = points * count;
        var replaced = 0;
        rows.forEach(function (r) { if (r.level === level && r.type === type) replaced = r.points * r.count; });
        var after = currentTotal() - replaced + rowScore;
        var msg = 'Adding this row: ' + count + ' \u00d7 ' + fmtPoints(points) + ' = ' + fmtPoints(rowScore) + (replaced ? ' (replaces ' + fmtPoints(replaced) + ')' : '') + '. Total score will be ' + fmtPoints(after) + '.';
        el.textContent = msg;
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

    if (bankEl) {
        [levelSel, typeSel].forEach(function (el) { el.addEventListener('change', updateBank); });
        countInput.addEventListener('input', updateBank);
        // Ticking or unticking a topic in Table 1 changes which questions count.
        form.addEventListener('change', function (e) {
            if (e.target && e.target.name === 'topicId[]') render();
        });
    }

    ['newBloomLevel', 'newQuestionType'].forEach(function (id) {
        document.getElementById(id).addEventListener('change', updatePending);
    });
    ['newPoints', 'newCount'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', updatePending);
    });

    // Optional "Fine-tune topic distribution" table: row totals, per-topic points and the grand total
    // follow every keystroke instead of waiting for the form to be saved.
    var matrixEl = document.getElementById('matrixTable');
    function updateMatrix() {
        if (!matrixEl) return;
        var perKey = {}, grand = 0;
        Array.prototype.forEach.call(matrixEl.querySelectorAll('tr[data-matrix-topic]'), function (tr) {
            var topicPoints = 0;
            Array.prototype.forEach.call(tr.querySelectorAll('input[data-matrix-key]'), function (inp) {
                var n = parseInt(inp.value, 10) || 0;
                var key = inp.getAttribute('data-matrix-key');
                perKey[key] = (perKey[key] || 0) + n;
                topicPoints += n * (parseFloat(inp.getAttribute('data-points')) || 0);
            });
            tr.querySelector('[data-topic-points]').textContent = fmtPoints(topicPoints);
            grand += topicPoints;
        });
        Array.prototype.forEach.call(matrixEl.querySelectorAll('[data-row-total]'), function (cell) {
            var key = cell.getAttribute('data-row-total');
            var got = perKey[key] || 0, want = parseInt(cell.getAttribute('data-expected'), 10) || 0;
            cell.textContent = got + (got === want ? '' : ' / ' + want);
            cell.className = 'work-num' + (got === want ? '' : ' work-bank-warn');
        });
        document.getElementById('matrixTotalPoints').textContent = fmtPoints(grand);
    }
    if (matrixEl) {
        matrixEl.addEventListener('input', updateMatrix);
        updateMatrix();
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
