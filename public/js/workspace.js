/* DESBank workspace: small progressive enhancements. Nothing here is required for a form to work. */
(function () {
    'use strict';

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
