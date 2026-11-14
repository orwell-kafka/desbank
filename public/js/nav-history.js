/*
 * Back buttons that behave like the browser's own history.
 *
 * Every page the app serves records itself in a per-tab trail (sessionStorage) and numbers its history
 * entry (history.state.dbNav). A link marked with data-back then jumps to the page the user actually
 * visited before this one with history.go(), so the earlier page comes back with its own URL, scroll
 * position and form state. It does not rebuild that page from a link.
 *
 *   - Entries with the same URL as the current page are skipped (e.g. a form that saves and reloads itself).
 *   - A page whose form was submitted is skipped as a Back target: Back after saving an edit goes to where the
 *     user was before opening that form, not to the stale form.
 *   - With no earlier page in this tab (a new tab, a direct visit, a sign-in), the link's own href is used
 *     as the fallback, so it still lands somewhere sensible.
 *
 * <script src="nav-history.js" data-reset> clears the trail (used by sign-in and sign-out pages).
 */
(function () {
    'use strict';

    var TRAIL = 'db.nav.trail';
    var CUR = 'db.nav.cur';
    var SUBMITTED = 'db.nav.submitted';
    var REPLACED = 'db.nav.replaced';
    var reset = !!(document.currentScript && document.currentScript.hasAttribute('data-reset'));

    function get(key) { try { return sessionStorage.getItem(key); } catch (e) { return null; } }
    function set(key, val) { try { if (val === null) sessionStorage.removeItem(key); else sessionStorage.setItem(key, String(val)); } catch (e) {} }
    function readTrail() {
        try { var t = JSON.parse(get(TRAIL)); return Array.isArray(t) ? t : []; } catch (e) { return []; }
    }
    function writeTrail(t) { set(TRAIL, JSON.stringify(t.slice(-100))); }
    function bare(url) { return String(url).split('#')[0]; }
    function currentPos() {
        var s = history.state;
        return s && typeof s === 'object' && typeof s.dbNav === 'number' ? s.dbNav : null;
    }
    function sameOriginReferrer() {
        try { return !!document.referrer && new URL(document.referrer).origin === location.origin; } catch (e) { return false; }
    }

    // Record this page in the trail. Runs on load and when the page is restored from the back/forward cache.
    function record() {
        var trail = readTrail();
        var pos = currentPos();
        var here = bare(location.href);

        var replaced = get(REPLACED) === '1';
        set(REPLACED, null);

        if (reset) {
            trail = [];
            pos = pos === null ? 0 : pos;
        } else if (replaced && pos === null && !isNaN(parseInt(get(CUR), 10))) {
            // This page took over the previous entry (location.replace): same position in the history.
            pos = parseInt(get(CUR), 10);
            trail = trail.filter(function (e) { return e.pos <= pos; });
            trail.forEach(function (e) { if (e.pos === pos) e.skip = false; });
        } else if (pos !== null) {
            // A numbered entry: reload, Back/Forward, or restored from cache. Everything after it is no longer behind us.
            trail = trail.filter(function (e) { return e.pos <= pos; });
        } else {
            // A brand-new history entry.
            var cur = parseInt(get(CUR), 10);
            if (history.length > 1 && sameOriginReferrer() && !isNaN(cur)) {
                pos = cur + 1;
                trail = trail.filter(function (e) { return e.pos < pos; });
                // The page that submitted a form is not somewhere to go "back" to.
                var sub = parseInt(get(SUBMITTED), 10);
                if (!isNaN(sub)) trail.forEach(function (e) { if (e.pos === sub) e.skip = true; });
            } else {
                pos = 0;        // new tab, direct visit or arrival from elsewhere: nothing behind us in the app
                trail = [];
            }
        }
        set(SUBMITTED, null);

        var entry = trail.filter(function (e) { return e.pos === pos; })[0];
        if (entry) entry.url = here; else trail.push({ pos: pos, url: here });

        var state = history.state && typeof history.state === 'object' ? history.state : {};
        state.dbNav = pos;
        try { history.replaceState(state, '', location.href); } catch (e) {}

        set(CUR, pos);
        writeTrail(trail);
    }

    // Replace the current history entry with another page, keeping the trail's numbering correct.
    window.dbNavReplace = function (url) {
        set(REPLACED, '1');
        window.location.replace(url);
    };

    record();
    window.addEventListener('pageshow', function (e) { if (e.persisted) record(); });

    // Remember that a form was sent, so the page it was sent from is skipped as a Back target.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || !form.getAttribute) return;
        if ((form.getAttribute('method') || 'get').toLowerCase() === 'get') return;
        var pos = currentPos();
        if (pos !== null) set(SUBMITTED, pos);
    });

    document.addEventListener('click', function (e) {
        var link = e.target.closest && e.target.closest('a[data-back]');
        if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (link.target && link.target !== '_self') return;

        var pos = currentPos();
        if (pos === null) return;                       // no trail: follow the href
        var here = bare(location.href);
        var trail = readTrail();

        for (var i = trail.length - 1; i >= 0; i--) {
            var t = trail[i];
            if (t.pos < pos && !t.skip && bare(t.url) !== here) {
                e.preventDefault();
                history.go(t.pos - pos);
                // If the browser could not go that far (its history was trimmed), fall back to the link.
                setTimeout(function () { window.location.href = link.href; }, 800);
                return;
            }
        }
        // Nothing earlier in this app: the href is the fallback page.
    });
})();
