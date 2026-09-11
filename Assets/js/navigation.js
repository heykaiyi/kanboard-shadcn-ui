/*!
 * Shadcn theme for Kanboard — navigation feedback and page titles
 *
 * Two small things Kanboard leaves to the browser:
 *
 *  - a progress line across the top while the next page loads, so a click
 *    that takes a moment does not look like nothing happened;
 *  - one title format for every page, built from the breadcrumb the theme
 *    already renders (Kanboard's own <title> varies by controller and is
 *    emitted before any hook this plugin can reach).
 */
(function () {
    'use strict';

    var SEPARATOR = ' › ';

    /* --------------------------------------------------------- favicon */

    /* app/Template/layout.php declares Kanboard's own icons before this
     * plugin's head hook runs. Ours are declared after, and the last
     * candidate of a given type normally wins — but "normally" is not a
     * guarantee across browsers, so the competitors are removed and ours
     * are left as the only ones. Browsers re-evaluate the icon set when
     * these links change.
     *
     * Ours are found by the marker Template/layout/head.php puts on them,
     * not by their URL: a bundled icon is a file under /plugins/Shadcn/,
     * but an uploaded one is served by BrandingController and its URL looks
     * nothing like that. Matching on the path deleted every candidate
     * including our own, and the browser fell through to /favicon.ico —
     * which is Kanboard's. */
    function claimFavicon() {
        var ours = document.querySelectorAll('link[data-sc-icon]');

        /* Nothing of ours to promote: leave the page's icons alone rather
         * than stripping the set and leaving none. */
        if (ours.length === 0) {
            return;
        }

        var links = document.querySelectorAll('link[rel~="icon"], link[rel~="apple-touch-icon"]');

        [].forEach.call(links, function (link) {
            if (! link.hasAttribute('data-sc-icon')) {
                link.parentNode.removeChild(link);
            }
        });
    }

    /* ---------------------------------------------------------- title */

    /* The instance's own name, from Settings → Appearance. It comes down as
     * the --sc-brand-title custom property — an inline <style> is allowed by
     * the content security policy and an inline <script> is not. The
     * template HTML-escapes it, and a <style> element does not decode
     * entities, so a textarea does that here. */
    function appName() {
        var value = getComputedStyle(document.documentElement)
            .getPropertyValue('--sc-brand-title').trim().replace(/^["']|["']$/g, '');
        var decoder = document.createElement('textarea');

        decoder.innerHTML = value;

        return decoder.value.trim() === '' ? 'Kanboard' : decoder.value.trim();
    }

    function setTitle() {
        var name = appName();
        /* The home crumb is the root, not part of the page's name. */
        var crumbs = document.querySelectorAll('.sc-crumbs li:not(.sc-crumb-home)');
        var parts = [];

        crumbs.forEach(function (li) {
            var text = li.textContent.trim();

            if (text !== '') {
                parts.push(text);
            }
        });

        if (parts.length > 0) {
            /* Deepest first: a browser tab shows the front of the string. */
            document.title = parts.reverse().join(SEPARATOR) + ' — ' + name;
            return;
        }

        /* No trail: the login and password-reset screens, a public board.
         * core/layout.php titles those with the page's own name, or — with
         * nothing else to say — the bare word "Kanboard", which is the one
         * place the product name stood in for the site's. */
        var current = document.title.replace(/\s+/g, ' ').trim();

        if (current === '' || current === 'Kanboard') {
            document.title = name;
        } else if (current.slice(-name.length) !== name) {
            document.title = current + ' — ' + name;
        }
    }

    /* -------------------------------------------------------- progress */

    var bar = null;
    var timer = null;

    function build() {
        if (bar !== null) {
            return bar;
        }

        bar = document.createElement('div');
        bar.className = 'sc-progress';
        document.body.appendChild(bar);

        return bar;
    }

    function start() {
        var element = build();

        window.clearTimeout(timer);
        element.classList.remove('is-done');
        /* restart the animation */
        void element.offsetWidth;
        element.classList.add('is-loading');
    }

    function done() {
        if (bar === null) {
            return;
        }

        bar.classList.remove('is-loading');
        bar.classList.add('is-done');
        timer = window.setTimeout(function () {
            bar.classList.remove('is-done');
        }, 400);
    }

    function isPlainNavigation(event, link) {
        if (event.defaultPrevented || event.button !== 0) {
            return false;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return false;
        }

        var href = link.getAttribute('href');

        if (href === null || href === '' || href.charAt(0) === '#') {
            return false;
        }

        if (link.target === '_blank' || link.hasAttribute('download')) {
            return false;
        }

        /* Anything Kanboard opens in place — modals, dropdowns, ajax — is not
         * a page load and must not raise the bar. */
        if (/js-modal|dropdown-menu|js-subtask|js-submit|js-reply/.test(link.className)) {
            return false;
        }

        return link.host === window.location.host;
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a[href]') : null;

        if (link !== null && isPlainNavigation(event, link)) {
            start();
        }
    });

    document.addEventListener('submit', function (event) {
        if (! event.defaultPrevented) {
            start();
        }
    });

    /* Coming back through the history cache leaves the bar mid-flight. */
    window.addEventListener('pageshow', done);

    function ready() {
        claimFavicon();
        setTitle();
        done();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready);
    } else {
        ready();
    }
})();
