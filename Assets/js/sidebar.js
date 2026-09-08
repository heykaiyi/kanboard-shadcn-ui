/*!
 * Shadcn theme for Kanboard — sidebar behaviour
 *
 * Two separate behaviours share one trigger, exactly as shadcn's sidebar-07
 * does: on a wide screen the sidebar collapses to a 3rem icon rail and the
 * choice is remembered, on a narrow screen it slides in over the page and is
 * always dismissed again on the next visit.
 *
 * The state lives on <html> rather than on the sidebar element so that the
 * page grid in sidebar.css can react to it, and so this file can set it from
 * <head> — before <body> is parsed — and avoid a first paint at the wrong
 * width.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'shadcn.sidebar.state';
    var COLLAPSED = 'collapsed';
    var EXPANDED = 'expanded';
    var root = document.documentElement;

    function readStoredState() {
        try {
            return window.localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function storeState(state) {
        try {
            window.localStorage.setItem(STORAGE_KEY, state);
        } catch (e) {
            /* private mode, or storage disabled — the sidebar still works */
        }
    }

    /* Restore before first paint. */
    if (readStoredState() === COLLAPSED) {
        root.setAttribute('data-sc-sidebar', COLLAPSED);
    }

    function isMobile() {
        return window.matchMedia('(max-width: 768px)').matches;
    }

    function isCollapsed() {
        return root.getAttribute('data-sc-sidebar') === COLLAPSED;
    }

    function setCollapsed(collapsed) {
        if (collapsed) {
            root.setAttribute('data-sc-sidebar', COLLAPSED);
        } else {
            root.removeAttribute('data-sc-sidebar');
        }

        storeState(collapsed ? COLLAPSED : EXPANDED);
    }

    function setMobileOpen(open) {
        if (open) {
            root.setAttribute('data-sc-sidebar-mobile', 'open');
        } else {
            root.removeAttribute('data-sc-sidebar-mobile');
        }
    }

    function isMobileOpen() {
        return root.getAttribute('data-sc-sidebar-mobile') === 'open';
    }

    function toggle() {
        if (isMobile()) {
            setMobileOpen(! isMobileOpen());
        } else {
            setCollapsed(! isCollapsed());
        }
    }

    function isTypingTarget(element) {
        if (! element) {
            return false;
        }

        var name = element.tagName;

        return name === 'INPUT' || name === 'TEXTAREA' || name === 'SELECT' || element.isContentEditable;
    }

    function ready() {
        var trigger = document.getElementById('sc-sidebar-trigger');
        var rail = document.getElementById('sc-sidebar-rail');
        var overlay = document.getElementById('sc-sidebar-overlay');
        var close = document.getElementById('sc-sidebar-close');

        if (trigger === null) {
            return;
        }

        function sync() {
            var expanded = isMobile() ? isMobileOpen() : ! isCollapsed();
            trigger.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }

        function handleToggle(event) {
            event.preventDefault();
            toggle();
            sync();
        }

        trigger.addEventListener('click', handleToggle);

        if (rail !== null) {
            rail.addEventListener('click', handleToggle);
        }

        if (overlay !== null) {
            overlay.addEventListener('click', function () {
                setMobileOpen(false);
                sync();
            });
        }

        /* The sheet's own dismiss control. It is only visible on a phone, so
         * it never competes with the rail. */
        if (close !== null) {
            close.addEventListener('click', function (event) {
                event.preventDefault();
                setMobileOpen(false);
                sync();
            });
        }

        document.addEventListener('keydown', function (event) {
            /* Cmd/Ctrl-B is shadcn's shortcut, but it is also "bold" inside a
             * text editor, so typing always wins. */
            if (event.key === 'Escape' && isMobileOpen()) {
                setMobileOpen(false);
                sync();
                return;
            }

            if ((event.metaKey || event.ctrlKey) && (event.key === 'b' || event.key === 'B')) {
                if (isTypingTarget(event.target)) {
                    return;
                }

                event.preventDefault();
                toggle();
                sync();
            }
        });

        /* Leaving the narrow breakpoint must not strand the off-canvas state. */
        window.matchMedia('(max-width: 768px)').addEventListener('change', function () {
            setMobileOpen(false);
            sync();
        });

        sync();
    }

    /* Kanboard's user menu opens with the full name alone on its first row.
     * shadcn's NavUser repeats the avatar and the address there, and both are
     * already on the page in the sidebar footer — so they are copied across
     * once Dropdown.js has cloned the menu to <body>. */
    function decorateUserMenu() {
        var row = document.querySelector('#dropdown ul li.no-hover');
        var meta = document.querySelector('.sc-sb-user-meta');

        if (row === null || meta === null || row.querySelector('.sc-menu-user') !== null) {
            return;
        }

        var name = row.querySelector('strong');

        if (name === null) {
            return;
        }

        var avatar = meta.querySelector('.sc-sb-user-avatar');
        var mail = meta.querySelector('.sc-sb-user-mail');

        var block = document.createElement('span');
        block.className = 'sc-menu-user';

        if (avatar !== null) {
            block.innerHTML = avatar.innerHTML;
        }

        var text = document.createElement('span');
        text.className = 'sc-menu-user-text';
        text.appendChild(name.cloneNode(true));

        if (mail !== null) {
            var address = document.createElement('span');
            address.className = 'sc-menu-user-mail';
            address.textContent = mail.textContent;
            text.appendChild(address);
        }

        block.appendChild(text);
        row.textContent = '';
        row.appendChild(block);
    }

    if (window.KB && typeof window.KB.on === 'function') {
        window.KB.on('dropdown.afterRender', decorateUserMenu);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready);
    } else {
        ready();
    }
})();
