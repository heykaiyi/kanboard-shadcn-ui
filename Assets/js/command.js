/*!
 * Shadcn theme for Kanboard — the command palette
 *
 * Cmd/Ctrl-K opens it, typing filters it, the arrows move through what is
 * left and Enter follows. The list itself is rendered by
 * Template/layout/command.php, because only the server knows which projects
 * this user may open.
 */
(function () {
    'use strict';

    var root = null;
    var input = null;
    var items = [];
    var visible = [];
    var index = 0;
    var lastFocus = null;
    var group = '';

    function collect() {
        root = document.getElementById('sc-command');

        if (root === null) {
            return false;
        }

        input = document.getElementById('sc-command-input');
        items = [].slice.call(root.querySelectorAll('.sc-command-item'));

        return true;
    }

    function highlight(next) {
        if (visible.length === 0) {
            return;
        }

        index = (next + visible.length) % visible.length;

        visible.forEach(function (item, i) {
            item.classList.toggle('is-active', i === index);
        });

        visible[index].scrollIntoView({ block: 'nearest' });
    }

    function filter(term) {
        var needle = term.trim().toLowerCase();

        visible = items.filter(function (item) {
            var inGroup = group === '' || item.closest('.sc-command-group').dataset.group === group;
            var match = inGroup && (needle === '' || item.dataset.label.indexOf(needle) !== -1);
            item.hidden = ! match;

            return match;
        });

        /* A group with nothing left in it should not leave its heading behind. */
        [].forEach.call(root.querySelectorAll('.sc-command-group'), function (group) {
            var any = group.querySelector('.sc-command-item:not([hidden])');
            group.hidden = any === null;
        });

        root.querySelector('.sc-command-empty').hidden = visible.length > 0;
        highlight(0);
    }

    /* `groupName` opens the palette already narrowed to one group. Nothing
     * in the theme passes one today — the top bar has no switcher of its own
     * — but the tabs are built from the groups, so this is the one place
     * that would answer such a control. */
    function open(groupName) {
        if (root === null && ! collect()) {
            return;
        }

        var target = groupName
            ? root.querySelector('.sc-command-tab[data-group="' + groupName.replace(/"/g, '\\"') + '"]')
            : null;

        lastFocus = document.activeElement;
        root.hidden = false;
        document.documentElement.classList.add('sc-command-open');
        input.value = '';
        group = target === null ? '' : groupName;
        [].forEach.call(root.querySelectorAll('.sc-command-tab'), function (tab, i) {
            tab.classList.toggle('is-active', target === null ? i === 0 : tab === target);
        });
        filter('');
        input.focus();
    }

    function close() {
        if (root === null || root.hidden) {
            return;
        }

        root.hidden = true;
        document.documentElement.classList.remove('sc-command-open');

        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
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
        if (! collect()) {
            return;
        }

        input.addEventListener('input', function () {
            filter(input.value);
        });

        /* Tabs narrow the same list the text box narrows; they compose. */
        root.addEventListener('click', function (event) {
            var tab = event.target.closest('.sc-command-tab');

            if (tab === null) {
                return;
            }

            group = tab.dataset.group;
            [].forEach.call(root.querySelectorAll('.sc-command-tab'), function (other) {
                other.classList.toggle('is-active', other === tab);
            });
            filter(input.value);
            input.focus();
        });

        root.addEventListener('click', function (event) {
            if (event.target.closest('[data-sc-command-close]')) {
                close();
            }
        });

        root.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                close();
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                highlight(index + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(index - 1);
            } else if (event.key === 'Enter' && visible.length > 0) {
                event.preventDefault();
                visible[index].click();
            }
        });

        document.addEventListener('keydown', function (event) {
            if ((event.metaKey || event.ctrlKey) && (event.key === 'k' || event.key === 'K')) {
                /* Inside the palette the shortcut closes it again. */
                if (! root.hidden) {
                    event.preventDefault();
                    close();

                    return;
                }

                if (isTypingTarget(event.target) && event.target !== input) {
                    return;
                }

                event.preventDefault();
                open();
            }
        });

        /* There are two: the one in the top bar, and the one inside the
         * mobile drawer — a phone has no ⌘K to press. Opening from the
         * drawer closes it, so the palette is not stacked on top of it. */
        [].forEach.call(document.querySelectorAll('.sc-command-trigger'), function (trigger) {
            trigger.addEventListener('click', function (event) {
                event.preventDefault();
                document.documentElement.removeAttribute('data-sc-sidebar-mobile');
                open(trigger.getAttribute('data-sc-command-group') || '');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ready);
    } else {
        ready();
    }
})();
