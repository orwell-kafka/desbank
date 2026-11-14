/*
 * Add-question form: save in the background, then replace this page with a fresh copy of the form.
 *
 * A normal save redirects to a new page, and that adds another history entry for the same address, so the
 * browser's Back button would step back through earlier copies of the form. Replacing the current entry keeps
 * the history as it was (question list -> add page), so Back goes straight to the list.
 *
 * Anything unexpected (validation errors, a network problem, no fetch support) falls back to a normal form
 * submit, which shows the server's error messages exactly as before.
 */
(function () {
    'use strict';
    var form = document.getElementById('questionForm');
    if (!form || !window.fetch || !window.FormData) return;

    form.addEventListener('submit', function (e) {
        if (e.defaultPrevented) return;          // already handled, e.g. a second click while saving
        e.preventDefault();

        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('save failed');
                return res.json();
            })
            .then(function (data) {
                if (!data || !data.redirect) throw new Error('unexpected reply');
                if (window.dbNavReplace) window.dbNavReplace(data.redirect);
                else window.location.replace(data.redirect);
            })
            .catch(function () {
                form.submit();                   // plain submit: the server shows any errors
            });
    });
})();
