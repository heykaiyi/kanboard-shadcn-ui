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

    /* The Turnstile challenge.
     *
     * The plugin renders it on `template:auth:login-form:after`, which is
     * outside </form> — so it arrives under the Sign-in button, which is
     * past the point anyone is still reading. It is moved in front of the
     * action, told to fill the column, and given the palette this page is
     * actually in rather than the operating system's.
     *
     * This runs while the document is still parsing, before Cloudflare's
     * async script has rendered anything, so the attributes are read at
     * render time. If it has already rendered, the node is still moved and
     * the attributes are left alone rather than fighting a live widget.
     */
    function placeChallenge() {
        var widget = document.querySelector('.form-login .cf-turnstile');

        if (widget === null) {
            return false;
        }

        if (widget.children.length === 0) {
            var scheme = prop('--sc-scheme');

            widget.setAttribute('data-size', 'flexible');
            widget.setAttribute('data-theme', scheme === 'dark' || scheme === 'auto' ? scheme : 'light');
        }

        var actions = document.querySelector('.form-login form .form-actions');

        if (actions !== null && widget.parentNode !== actions.parentNode) {
            actions.parentNode.insertBefore(widget, actions);
        }

        return true;
    }

    /* This script is in <head>, so the widget does not exist yet — and
     * Cloudflare's own script is `async`, so waiting for DOMContentLoaded
     * would be a race against it. An observer catches the element the moment
     * it is parsed, which is long before a network fetch can return. */
    if (! placeChallenge() && typeof MutationObserver === 'function') {
        var challengeObserver = new MutationObserver(function () {
            if (placeChallenge()) {
                challengeObserver.disconnect();
            }
        });

        challengeObserver.observe(document.documentElement, { childList: true, subtree: true });
        document.addEventListener('DOMContentLoaded', function () {
            placeChallenge();
            challengeObserver.disconnect();
        });
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
