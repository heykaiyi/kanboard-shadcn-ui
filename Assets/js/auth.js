/*!
 * Shadcn theme for Kanboard — the auth screens' legal footer.
 *
 * The login screen has a hook to render into (template:auth:login-form:before);
 * the two password-reset screens have none, and Kanboard draws them with
 * no_layout so template:layout:top never fires either. Real links cannot come
 * from a pseudo-element, so the footer is built here instead — on every screen
 * that shows .form-login, from strings the head template hands over as custom
 * properties (an inline <style> is allowed by the policy, an inline <script>
 * is not).
 */
(function () {
    'use strict';

    function prop(name) {
        var value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

        // Custom properties carrying text arrive quoted.
        return value.replace(/^["']|["']$/g, '');
    }

    function link(label, href) {
        var node = document.createElement(href === '' ? 'span' : 'a');

        if (href !== '') {
            node.href = href;
        }

        node.textContent = label;

        return node;
    }

    /* The login screen renders the brand through its own hook. The two
     * password-reset screens have none, and a pseudo-element cannot be a
     * link — so the same block is built here, from the strings the head
     * template hands over, and lands in the same place: first child of the
     * form column. The CSS fallback stands down on its own, because it is
     * written as :not(:has(.sc-auth-brand)). */
    function brand() {
        var wrapper = document.querySelector('.form-login');

        if (wrapper === null || document.querySelector('.sc-auth-brand') !== null) {
            return;
        }

        var title = prop('--sc-brand-title');

        if (title === '') {
            return;
        }

        var node = document.createElement('a');
        node.className = 'sc-auth-brand';
        node.href = prop('--sc-brand-url') || '/';

        var mark = document.createElement('span');
        mark.className = 'sc-auth-brand-mark';
        mark.setAttribute('aria-hidden', 'true');

        var text = document.createElement('span');
        text.className = 'sc-auth-brand-title';
        text.textContent = title;

        node.appendChild(mark);
        node.appendChild(text);
        wrapper.insertBefore(node, wrapper.firstChild);
    }

    function build() {
        brand();

        var wrapper = document.querySelector('.form-login');

        if (wrapper === null || document.querySelector('.sc-auth-legal') !== null) {
            return;
        }

        var terms = prop('--sc-legal-terms');
        var privacy = prop('--sc-legal-privacy');

        if (terms === '' && privacy === '') {
            return;
        }

        var footer = document.createElement('p');
        footer.className = 'sc-auth-legal';

        if (terms !== '') {
            footer.appendChild(link(terms, prop('--sc-legal-terms-url')));
        }

        if (terms !== '' && privacy !== '') {
            footer.appendChild(document.createTextNode(' · '));
        }

        if (privacy !== '') {
            footer.appendChild(link(privacy, prop('--sc-legal-privacy-url')));
        }

        wrapper.appendChild(footer);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', build);
    } else {
        build();
    }
})();
