/*!
 * Shadcn theme for Kanboard — two controls Kanboard prints as plain markup
 *
 * Input OTP. The two-factor code is one bare text field, and it stays
 * exactly that — the field that submits, autofills and takes a paste. It is
 * laid transparently over six drawn slots, and each digit is written into
 * its slot as it is typed, which is how shadcn's InputOTP is built too.
 * The "Test your device" field on the settings screen passes its
 * autocomplete attribute in the wrong argument, so it carries none; both
 * fields are name="code" with class form-numeric, which is what catches it.
 *
 * Kbd. The keyboard-shortcut sheet prints `Label = <strong>v o</strong>`.
 * Each <strong> becomes one <kbd> per key, and each row a label beside its
 * keys.
 *
 * Both apply where the markup stands and to markup a sheet loads later,
 * the same way password.js does.
 */
(function () {
    'use strict';

    var OTP_LENGTH = 6;
    var OTP_SELECTOR = 'input[autocomplete="one-time-code"], input.form-numeric[name="code"]';

    /* ------------------------------------------------------------ OTP */

    function enhanceOtp(input) {
        if (input.dataset.scOtp === 'on') {
            return;
        }

        input.dataset.scOtp = 'on';
        input.setAttribute('maxlength', String(OTP_LENGTH));

        /* The field arrives with autofocus, and moving a focused element in
         * the DOM blurs it — so focus is put back once it is wrapped. */
        var hadFocus = document.activeElement === input;

        var wrapper = document.createElement('span');
        wrapper.className = 'sc-otp';

        var slots = document.createElement('span');
        slots.className = 'sc-otp-slots';
        slots.setAttribute('aria-hidden', 'true');

        for (var i = 0; i < OTP_LENGTH; i++) {
            var slot = document.createElement('span');
            slot.className = 'sc-otp-slot';
            slots.appendChild(slot);
        }

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        wrapper.appendChild(slots);

        function render() {
            var digits = input.value.replace(/\s+/g, '').slice(0, OTP_LENGTH);
            var focused = document.activeElement === input;
            var active = Math.min(digits.length, OTP_LENGTH - 1);

            Array.prototype.forEach.call(slots.children, function (node, index) {
                node.textContent = digits.charAt(index);
                node.classList.toggle('is-active', focused && index === active);
            });
        }

        ['input', 'focus', 'blur', 'keyup'].forEach(function (name) {
            input.addEventListener(name, render);
        });

        if (hadFocus) {
            input.focus();
        }

        render();
    }

    /* ------------------------------------------------------------ Kbd */

    function keyLabel(key) {
        return key.length > 1 ? key.charAt(0) + key.slice(1).toLowerCase() : key;
    }

    /* "CTRL+ENTER" is a chord and "v o" a sequence; both are drawn as a row
     * of keys, the way shadcn's KbdGroup draws them. */
    function kbdGroup(strong) {
        var text = strong.textContent.trim();
        var keys = text.length > 1 && text.indexOf('+') !== -1 ? text.split('+') : text.split(/\s+/);
        var group = document.createElement('span');

        group.className = 'sc-kbd-group';

        keys.forEach(function (key) {
            key = key.trim();

            if (key === '') {
                return;
            }

            var kbd = document.createElement('kbd');
            kbd.textContent = keyLabel(key);
            group.appendChild(kbd);
        });

        return group;
    }

    /* Recognised by shape rather than by a class the template does not have:
     * a panel where every row names a key after " = ". */
    function isShortcutPanel(panel) {
        var items = panel.querySelectorAll('li');

        if (items.length === 0) {
            return false;
        }

        return Array.prototype.every.call(items, function (item) {
            return item.querySelector('strong') !== null && item.textContent.indexOf(' = ') !== -1;
        });
    }

    function decorateShortcutRow(item) {
        var label = document.createElement('span');
        var keys = document.createElement('span');
        var pastLabel = false;

        label.className = 'sc-shortcut-label';
        keys.className = 'sc-shortcut-keys';

        Array.prototype.slice.call(item.childNodes).forEach(function (node) {
            if (! pastLabel) {
                if (node.nodeType === 3 && node.nodeValue.indexOf(' = ') !== -1) {
                    label.appendChild(document.createTextNode(node.nodeValue.slice(0, node.nodeValue.indexOf(' = ')).trim()));
                    pastLabel = true;
                } else {
                    label.appendChild(node);
                }

                return;
            }

            if (node.nodeName === 'STRONG') {
                keys.appendChild(kbdGroup(node));
            } else if (node.nodeType === 3) {
                /* The " or " between two chords. */
                if (node.nodeValue.trim() !== '') {
                    keys.appendChild(document.createTextNode(node.nodeValue.trim()));
                }
            } else {
                keys.appendChild(node);
            }
        });

        item.textContent = '';
        item.classList.add('sc-shortcut');
        item.appendChild(label);
        item.appendChild(keys);
    }

    function decorateShortcuts(root) {
        var panels = root.matches && root.matches('.panel') ? [root] : root.querySelectorAll('.panel');

        Array.prototype.forEach.call(panels, function (panel) {
            if (panel.dataset.scShortcuts === 'on' || ! isShortcutPanel(panel)) {
                return;
            }

            panel.dataset.scShortcuts = 'on';
            panel.classList.add('sc-shortcuts');
            Array.prototype.forEach.call(panel.querySelectorAll('li'), decorateShortcutRow);
        });
    }

    /* ----------------------------------------------------------- scan */

    function scan(root) {
        if (root.matches && root.matches(OTP_SELECTOR)) {
            enhanceOtp(root);
        } else {
            Array.prototype.forEach.call(root.querySelectorAll(OTP_SELECTOR), enhanceOtp);
        }

        decorateShortcuts(root);
    }

    scan(document);

    /* Both enhancements are mutations the observer then sees; the flags on
     * the field and on the panel are what stop that from going anywhere. */
    if (typeof MutationObserver === 'function') {
        new MutationObserver(function (records) {
            records.forEach(function (record) {
                Array.prototype.forEach.call(record.addedNodes, function (node) {
                    if (node.nodeType === 1) {
                        scan(node);
                    }
                });
            });
        }).observe(document.documentElement, { childList: true, subtree: true });
    }
})();
