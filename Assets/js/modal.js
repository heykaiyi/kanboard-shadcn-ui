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

    /* Nothing behind a sheet scrolls while it is open.
     *
     * Kanboard leaves the page scrollable under its modal, so a wheel over
     * the scrim moved the board behind it and the sheet's own scroll handed
     * off to the page as soon as it reached its end. The class is toggled
     * from the overlay's presence rather than from an event, because a modal
     * is destroyed by several paths — the close button, Escape, a click on
     * the scrim, and a form that replaces itself — and only one of them
     * announces it. */
    function lockScroll() {
        root.classList.toggle('sc-modal-open', document.getElementById('modal-overlay') !== null);
    }

    if (typeof MutationObserver === 'function') {
        new MutationObserver(lockScroll).observe(document.documentElement, { childList: true, subtree: true });
    }

    lockScroll();

    if (window.KB && typeof window.KB.on === 'function') {
        window.KB.on('modal.beforeDestroy', clear);
        window.KB.on('modal.close', clear);
    }

    /* Clicking the scrim closes the sheet — always.
     *
     * core/modal.js already binds this, but it refuses once anything in the
     * form has fired a change event: `isFormDirty` is set on the first
     * keystroke and never cleared, so from then on the only way out is the
     * X. Dismissing by clicking outside is what a Sheet, a Dialog and a
     * Drawer all do, so this listener runs in the capture phase and closes
     * it before core's own handler declines to.
     */
    document.addEventListener('click', function (event) {
        if (event.target.id !== 'modal-overlay') {
            return;
        }

        if (window.KB && window.KB.modal && typeof window.KB.modal.close === 'function') {
            event.preventDefault();
            event.stopPropagation();
            window.KB.modal.close();
        }
    }, true);
})();
