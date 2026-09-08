/*!
 * Shadcn theme for Kanboard — the notification Sheet
 *
 * Every modal Kanboard opens shares one #modal-box with no distinguishing
 * mark, so a stylesheet cannot single out the notification panel. The link
 * that opened it can be recognised though, so the click is noted and the
 * flag lives on <html> until the modal is torn down — which is all the CSS
 * needs to switch that one dialog to a Sheet.
 */
(function () {
    'use strict';

    var FLAG = 'scModal';
    var root = document.documentElement;

    /* Matched on the element the link sits in rather than on its href: with
     * URL rewriting on, that href is /user/1/notifications/web and carries no
     * controller name to look for. */
    var SHEET_SELECTOR = '.notification a';

    document.addEventListener('click', function (event) {
        var link = event.target.closest ? event.target.closest('a[href]') : null;

        if (link === null) {
            return;
        }

        if (link.matches(SHEET_SELECTOR) || link.closest('.notification') !== null) {
            root.dataset[FLAG] = 'sheet';
        } else if (String(link.className).indexOf('js-modal') !== -1) {
            delete root.dataset[FLAG];
        }
    }, true);

    function clear() {
        delete root.dataset[FLAG];
    }

    if (window.KB && typeof window.KB.on === 'function') {
        window.KB.on('modal.beforeDestroy', clear);
        window.KB.on('modal.close', clear);
    }
})();
