/*!
 * Shadcn theme for Kanboard — Settings → Appearance
 *
 * Everything on that screen is something you judge by looking at it, so the
 * screen answers before the save does: the swatch and the hex field are two
 * views of one value, the preview row takes the colour as you pick it, and a
 * chosen file is drawn in its plate before it is uploaded.
 *
 * Nothing here is required. Without it the swatch is simply a second input
 * that is not submitted, and the file inputs are ordinary file inputs.
 */
(function () {
    'use strict';

    var form = document.querySelector('form.sc-brand');

    if (form === null) {
        return;
    }

    var hex = form.querySelector('.sc-brand-hex');
    var swatch = form.querySelector('.sc-brand-swatch');
    var reset = form.querySelector('[data-sc-brand-reset]');
    var preview = form.querySelector('.sc-brand-preview');

    /* The same rule BrandingModel applies on the server: whichever of black
     * or white has the better contrast ratio against the accent, so a pale
     * brand colour gets dark labels instead of unreadable white ones. */
    function foreground(color) {
        var channels = [
            parseInt(color.substr(1, 2), 16) / 255,
            parseInt(color.substr(3, 2), 16) / 255,
            parseInt(color.substr(5, 2), 16) / 255
        ].map(function (channel) {
            return channel <= 0.03928
                ? channel / 12.92
                : Math.pow((channel + 0.055) / 1.055, 2.4);
        });

        var luminance = 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];

        return (luminance + 0.05) / 0.05 >= 1.05 / (luminance + 0.05) ? '#0a0a0a' : '#ffffff';
    }

    /* `#abc`, `#AABBCC` and a bare `aabbcc` all mean the same thing to
     * someone typing one in — the same three forms the server accepts.
     * Anything else is not a colour yet, and the preview simply waits. */
    function normalize(value) {
        value = String(value).trim().replace(/^#/, '');

        if (/^[0-9a-f]{3}$/i.test(value)) {
            value = value[0] + value[0] + value[1] + value[1] + value[2] + value[2];
        }

        return /^[0-9a-f]{6}$/i.test(value) ? '#' + value.toLowerCase() : '';
    }

    function paint(color) {
        if (preview !== null) {
            preview.style.setProperty('--sc-brand-preview', color);
            preview.style.setProperty('--sc-brand-preview-fg', foreground(color));
        }

        if (swatch !== null) {
            swatch.value = color;
        }
    }

    function fallback() {
        return hex !== null && hex.placeholder !== '' ? hex.placeholder : '#1145af';
    }

    if (hex !== null) {
        hex.addEventListener('input', function () {
            var color = normalize(hex.value);
            paint(color !== '' ? color : fallback());
        });
    }

    if (swatch !== null && hex !== null) {
        swatch.addEventListener('input', function () {
            hex.value = swatch.value;
            paint(swatch.value);
        });
    }

    /* Back to the theme's own colour, which is an empty field rather than
     * the default written out — "reset" and "never set" are one state. */
    if (reset !== null && hex !== null) {
        reset.addEventListener('click', function () {
            hex.value = '';
            paint(reset.getAttribute('data-default') || fallback());
        });
    }

    /* The chosen file, drawn where the current one is. The object URL is
     * released when it is replaced, so picking a file five times in a row
     * does not hold five images. */
    Array.prototype.forEach.call(form.querySelectorAll('[data-sc-brand-file]'), function (input) {
        var key = input.getAttribute('data-sc-brand-file');
        var image = form.querySelector('[data-sc-brand-preview="' + key + '"]');
        var name = form.querySelector('[data-sc-brand-name="' + key + '"]');
        var url = '';

        input.addEventListener('change', function () {
            var file = input.files && input.files.length > 0 ? input.files[0] : null;

            if (name !== null) {
                name.textContent = file === null ? name.getAttribute('data-empty') || name.textContent : file.name;
            }

            if (file === null || image === null) {
                return;
            }

            if (url !== '') {
                URL.revokeObjectURL(url);
            }

            url = URL.createObjectURL(file);
            image.src = url;
        });

        if (name !== null) {
            name.setAttribute('data-empty', name.textContent);
        }
    });
})();
