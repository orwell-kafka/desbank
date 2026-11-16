/*
 * Exam page: "Choose…" opens the replacement list in a pop-up instead of going to another page.
 *
 * The link keeps its normal href, so without JavaScript (or if loading fails) it still opens the
 * full "Choose a replacement" page. Picking "Use this" submits normally and the exam page reloads
 * with the question swapped; the scroll position is restored so the person stays where they were.
 */
(function () {
    'use strict';
    if (!window.fetch) return;

    var SCROLL_KEY = 'dbExamScroll:' + location.pathname;
    var dialog = null, body = null, trigger = null, requestId = 0;

    // Back on the exam page after a replacement: return to the same spot.
    try {
        var saved = sessionStorage.getItem(SCROLL_KEY);
        if (saved !== null) {
            sessionStorage.removeItem(SCROLL_KEY);
            window.addEventListener('load', function () { window.scrollTo(0, parseInt(saved, 10) || 0); });
        }
    } catch (e) { /* storage unavailable: just stay at the top */ }

    function build() {
        dialog = document.createElement('dialog');
        dialog.className = 'work-picker';
        dialog.setAttribute('aria-labelledby', 'work-picker-title');
        dialog.innerHTML =
            '<div class="work-picker-head">' +
                '<h2 id="work-picker-title">Choose a replacement</h2>' +
                '<button type="button" class="work-picker-close" data-picker-close aria-label="Close">&times;</button>' +
            '</div>' +
            '<div class="work-picker-body" aria-live="polite"></div>';
        document.body.appendChild(dialog);
        body = dialog.querySelector('.work-picker-body');

        dialog.addEventListener('click', function (e) {
            if (e.target === dialog || e.target.closest('[data-picker-close]')) dialog.close();   // backdrop or ×
        });
        dialog.addEventListener('close', function () {
            requestId++;                                          // ignore a reply that arrives late
            document.documentElement.classList.remove('work-modal-open');
            if (trigger && document.contains(trigger)) trigger.focus();
        });

        // Search box inside the list.
        dialog.addEventListener('input', function (e) {
            if (e.target.id !== 'candidateFilter') return;
            var q = e.target.value.trim().toLowerCase();
            var shown = 0;
            body.querySelectorAll('#candidateTable tbody tr').forEach(function (row) {
                var match = !q || row.textContent.toLowerCase().indexOf(q) !== -1;
                row.hidden = !match;
                if (match) shown++;
            });
            var count = body.querySelector('#candidateCount');
            var none = body.querySelector('#candidateNone');
            if (count) count.textContent = shown + (shown === 1 ? ' question' : ' questions');
            if (none) none.hidden = shown !== 0;
        });

        // "Use this": same pending feedback as other forms, and remember the scroll position.
        dialog.addEventListener('submit', function (e) {
            var form = e.target;
            if (form.dataset.busy) { e.preventDefault(); return; }
            form.dataset.busy = '1';
            var btn = e.submitter || form.querySelector('[type="submit"]');
            if (btn) {
                btn.classList.add('is-pending');
                btn.setAttribute('aria-busy', 'true');
                if (btn.dataset.pendingLabel) btn.textContent = btn.dataset.pendingLabel;
            }
            try { sessionStorage.setItem(SCROLL_KEY, String(window.scrollY)); } catch (err) { /* ignore */ }
        });
    }

    function open(link) {
        if (!dialog) build();
        trigger = link;
        var id = ++requestId;
        body.innerHTML = '<p class="work-picker-loading" role="status">Loading questions…</p>';
        document.documentElement.classList.add('work-modal-open');
        if (!dialog.open) dialog.showModal();

        var url = link.href + (link.href.indexOf('?') === -1 ? '?' : '&') + 'modal=1';
        fetch(url, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('load failed');
                return res.text();
            })
            .then(function (html) {
                if (id !== requestId) return;                      // closed or replaced meanwhile
                body.innerHTML = html;
                var search = body.querySelector('#candidateFilter');
                if (search) search.focus();
            })
            .catch(function () {
                if (id !== requestId) return;
                window.location.href = link.href;                  // fall back to the full page
            });
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-replace-modal]');
        if (!link) return;
        if (e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;   // let "open in new tab" work
        e.preventDefault();
        open(link);
    });
})();
