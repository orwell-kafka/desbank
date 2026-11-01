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
})();
