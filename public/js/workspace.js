/* DESBank workspace: small progressive enhancements. Nothing here is required for a form to work. */
(function () {
    'use strict';


    // 0. In-page confirmation instead of the browser's window.confirm().
    //    Any <form data-confirm="Delete this topic?"> asks first. Optional attributes:
    //      data-confirm-item   the record's name, shown in bold
    //      data-confirm-note   extra sentence (default for danger: "This can't be undone."; empty = none)
    //      data-confirm-label  text of the confirm button (default "Confirm")
    //      data-confirm-tone   "danger" (default) or "neutral"
    //    Registered in the capture phase so it runs before the pending-state handler below.
    var dialog = null, resolveAsk = null, lastFocus = null;

    function buildDialog() {
        dialog = document.createElement('dialog');
        dialog.className = 'work-confirm';
        dialog.setAttribute('aria-labelledby', 'work-confirm-title');
        dialog.setAttribute('aria-describedby', 'work-confirm-text');
        dialog.innerHTML =
            '<div class="work-confirm-panel">' +
            '<h2 id="work-confirm-title"></h2>' +
            '<p id="work-confirm-text"><strong class="work-confirm-item"></strong><span class="work-confirm-note"></span></p>' +
            '<div class="work-confirm-actions">' +
            '<button type="button" class="work-action-outline" data-act="cancel">Cancel</button>' +
            '<button type="button" class="work-action-primary work-confirm-ok" data-act="ok"></button>' +
            '</div></div>';
        document.body.appendChild(dialog);

        dialog.addEventListener('click', function (e) {
            var act = e.target.getAttribute && e.target.getAttribute('data-act');
            if (act === 'ok') dialog.close('ok');
            else if (act === 'cancel' || e.target === dialog) dialog.close('cancel');   // backdrop click
        });
        dialog.addEventListener('keydown', function (e) {           // keep Tab inside the dialog
            if (e.key !== 'Tab') return;
            var buttons = dialog.querySelectorAll('button');
            var first = buttons[0], last = buttons[buttons.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        });
        dialog.addEventListener('close', function () {
            var ok = dialog.returnValue === 'ok';
            dialog.returnValue = '';
            if (lastFocus && !ok && document.contains(lastFocus)) lastFocus.focus();
            if (resolveAsk) { var r = resolveAsk; resolveAsk = null; r(ok); }
        });
    }

    function ask(form) {
        var title = form.getAttribute('data-confirm') || 'Are you sure?';
        var item = form.getAttribute('data-confirm-item') || '';
        var tone = form.getAttribute('data-confirm-tone') || 'danger';
        var label = form.getAttribute('data-confirm-label') || 'Confirm';
        var note = form.hasAttribute('data-confirm-note')
            ? form.getAttribute('data-confirm-note')
            : (tone === 'danger' ? "This can't be undone." : '');

        if (typeof HTMLDialogElement === 'undefined') {            // very old browsers
            return Promise.resolve(window.confirm(title + (item ? '\n' + item : '')));
        }
        if (!dialog) buildDialog();

        dialog.querySelector('#work-confirm-title').textContent = title;
        dialog.querySelector('.work-confirm-item').textContent = item;
        dialog.querySelector('.work-confirm-item').hidden = !item;
        dialog.querySelector('.work-confirm-note').textContent = note;
        var ok = dialog.querySelector('.work-confirm-ok');
        ok.textContent = label;
        ok.classList.toggle('is-danger', tone === 'danger');

        lastFocus = document.activeElement;
        return new Promise(function (resolve) {
            resolveAsk = resolve;
            dialog.showModal();
            dialog.querySelector('[data-act="cancel"]').focus();   // safe default
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.hasAttribute || !form.hasAttribute('data-confirm')) return;
        if (form.dataset.confirmed === '1') { delete form.dataset.confirmed; return; }   // second pass, already confirmed
        e.preventDefault();
        e.stopImmediatePropagation();
        var submitter = e.submitter || null;
        ask(form).then(function (ok) {
            if (!ok) return;
            form.dataset.confirmed = '1';
            if (form.requestSubmit) form.requestSubmit(submitter || undefined);
            else form.submit();
        });
    }, true);

    // 1. Pending feedback on submit. The button is NOT disabled, so the browser always
    //    submits normally; a second submit is simply ignored while one is in flight.
    document.querySelectorAll('form[data-pending]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;                 // e.g. a cancelled confirm()
            if (form.dataset.busy) { e.preventDefault(); return; }
            var btn = e.submitter || form.querySelector('[type="submit"]');
            if (!btn) return;
            form.dataset.busy = '1';
            btn.dataset.label = btn.textContent;
            btn.classList.add('is-pending');
            btn.setAttribute('aria-busy', 'true');
            if (btn.dataset.pendingLabel) btn.textContent = btn.dataset.pendingLabel;
        });
        // Back/forward cache restores the page as it was: reset the button.
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            delete form.dataset.busy;
            form.querySelectorAll('.is-pending').forEach(function (btn) {
                btn.classList.remove('is-pending');
                btn.removeAttribute('aria-busy');
                if (btn.dataset.label) btn.textContent = btn.dataset.label;
            });
        });
    });

    // 2. Filter selects auto-submit (inline onchange). Dim the results while the page reloads.
    document.querySelectorAll('[data-filter-form]').forEach(function (form) {
        var region = document.querySelector(form.getAttribute('data-filter-form'));
        form.addEventListener('change', function () {
            if (region) region.setAttribute('aria-busy', 'true');
        });
        window.addEventListener('pageshow', function (e) {
            if (e.persisted && region) region.removeAttribute('aria-busy');
        });
    });

    // 3. Clickable validation messages: jump to (and focus) the field or section that needs fixing.
    function esc(v) { return window.CSS && CSS.escape ? CSS.escape(v) : String(v).replace(/["\\]/g, '\\$&'); }
    function isVisible(el) { return !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length); }

    function findErrorTarget(key) {
        var base = String(key).split('.')[0];
        var selectors = [
            '[data-error-key~="' + esc(key) + '"]',
            '[data-error-key~="' + esc(base) + '"]',
            '[name="' + esc(base) + '"]',
            '[name="' + esc(base) + '[]"]',
            '[id="' + esc(base) + '"]'
        ];
        for (var i = 0; i < selectors.length; i++) {
            var found = document.querySelectorAll(selectors[i]);
            for (var j = 0; j < found.length; j++) {
                if (found[j].type !== 'hidden' && isVisible(found[j])) return found[j];
            }
        }
        return null;
    }

    function focusable(el) {
        if (/^(INPUT|SELECT|TEXTAREA|BUTTON|A)$/.test(el.tagName) || el.hasAttribute('tabindex')) return el;
        return el.querySelector('input:not([type="hidden"]), select, textarea, button') || el;
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('[data-error-target]');
        if (!link) return;
        e.preventDefault();
        var target = findErrorTarget(link.getAttribute('data-error-target'));
        if (!target) return;
        var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
        target.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
        var toFocus = focusable(target);
        if (!toFocus.hasAttribute('tabindex') && !/^(INPUT|SELECT|TEXTAREA|BUTTON|A)$/.test(toFocus.tagName)) toFocus.setAttribute('tabindex', '-1');
        toFocus.focus({ preventScroll: true });
        var flash = target.closest('.work-field, .work-section') || target;
        flash.classList.remove('is-flash');
        void flash.offsetWidth;
        flash.classList.add('is-flash');
        setTimeout(function () { flash.classList.remove('is-flash'); }, 2200);
    });
})();
