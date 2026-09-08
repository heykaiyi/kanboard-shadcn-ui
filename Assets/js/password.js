/*!
 * Shadcn theme for Kanboard — show the password
 *
 * Every password field in Kanboard is a bare <input type="password"> with no
 * way to check what was typed, which is the one thing that makes a long
 * password on a phone keyboard bearable. shadcn's Input has the control on
 * the right of the field, so this puts one there.
 *
 * No template is overridden: the input is wrapped where it stands and the
 * button is inserted beside it. Fields that arrive later — a sheet loaded
 * over Ajax, the second field a form reveals — are picked up by an observer,
 * so there is nothing to call again after a page changes.
 *
 * The field goes back to type="password" as soon as the form is submitted:
 * a browser will not offer to save a password it can see as plain text, and
 * some password managers stop watching the field entirely.
 */
(function () {
    'use strict';

    var FLAG = 'scPassword';

    /* The copy comes down as custom properties on :root — an inline <script>
     * is not permitted by the content security policy and an inline <style>
     * is, which is the same channel auth.js reads its strings from. */
    function prop(name, fallback) {
        var value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

        return value === '' ? fallback : value.replace(/^["']|["']$/g, '');
    }

    function label(shown) {
        return shown ? prop('--sc-pw-hide', 'Hide password') : prop('--sc-pw-show', 'Show password');
    }

    function enhance(input) {
        if (input.dataset[FLAG] === 'on' || input.type !== 'password') {
            return;
        }

        input.dataset[FLAG] = 'on';

        var wrapper = document.createElement('span');
        wrapper.className = 'sc-pw';

        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'sc-pw-toggle';
        button.setAttribute('aria-label', label(false));
        button.setAttribute('aria-pressed', 'false');
        /* The field is what a keyboard user is in; the control beside it is
         * reachable, but it does not stand between the field and Submit. */
        button.tabIndex = -1;

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        wrapper.appendChild(button);

        button.addEventListener('click', function () {
            var shown = input.type === 'text';

            input.type = shown ? 'password' : 'text';
            button.setAttribute('aria-pressed', shown ? 'false' : 'true');
            button.setAttribute('aria-label', label(! shown));
            wrapper.classList.toggle('sc-pw-shown', ! shown);

            /* Put the caret back where it was: changing the type moves it to
             * the end in every browser. */
            input.focus();

            if (typeof input.selectionStart === 'number') {
                var end = input.value.length;
                input.setSelectionRange(end, end);
            }
        });

        var form = input.form;

        if (form !== null && form.dataset[FLAG] !== 'on') {
            form.dataset[FLAG] = 'on';
            form.addEventListener('submit', function () {
                Array.prototype.forEach.call(form.querySelectorAll('.sc-pw > input[type="text"]'), function (field) {
                    field.type = 'password';
                });
            });
        }
    }

    function scan(root) {
        Array.prototype.forEach.call(root.querySelectorAll('input[type="password"]'), enhance);
    }

    scan(document);

    /* Wrapping an input is itself a mutation, so the observer sees its own
     * work — the flag on the input is what stops that from going anywhere. */
    if (typeof MutationObserver === 'function') {
        new MutationObserver(function (records) {
            records.forEach(function (record) {
                Array.prototype.forEach.call(record.addedNodes, function (node) {
                    if (node.nodeType !== 1) {
                        return;
                    }

                    if (node.matches('input[type="password"]')) {
                        enhance(node);
                    } else {
                        scan(node);
                    }
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }
})();
