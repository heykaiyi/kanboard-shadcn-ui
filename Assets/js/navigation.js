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

    var APP_NAME = 'Kanboard';
    var SEPARATOR = ' › ';

    /* --------------------------------------------------------- favicon */

    /* app/Template/layout.php declares Kanboard's own icons before this
     * plugin's head hook runs. Ours are declared after, and the last
     * candidate of a given type normally wins — but "normally" is not a
     * guarantee across browsers, so the competitors are removed and ours
     * are left as the only ones. Browsers re-evaluate the icon set when
     * these links change. */
    function claimFavicon() {
        var links = document.querySelectorAll('link[rel~="icon"], link[rel~="apple-touch-icon"]');

        [].forEach.call(links, function (link) {
            var href = link.getAttribute('href') || '';

            if (href.indexOf('/plugins/Shadcn/') === -1) {
                link.parentNode.removeChild(link);
            }
        });
    }

    /* ---------------------------------------------------------- title */

    function setTitle() {
        /* The home crumb is the root, not part of the page's name. */
        var crumbs = document.querySelectorAll('.sc-crumbs li:not(.sc-crumb-home)');

        if (crumbs.length === 0) {
            return;
        }

        var parts = [];

        crumbs.forEach(function (li) {
            var text = li.textContent.trim();

            if (text !== '') {
                parts.push(text);
            }
        });

        if (parts.length === 0) {
            return;
        }

        /* Deepest first: a browser tab shows the front of the string. */
        document.title = parts.reverse().join(SEPARATOR) + ' — ' + APP_NAME;
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
