<?php

namespace Kanboard\Plugin\Shadcn\Model;

use Kanboard\Core\Base;
use RuntimeException;

/**
 * Everything an instance is allowed to make its own.
 *
 * The theme itself is deliberately not configurable — a settings screen with
 * forty colour pickers is a way to build an ugly instance, not a branded one.
 * What is configurable is the short list that actually says whose instance
 * this is: the mark, how much of the lockup is drawn and how large the mark
 * is drawn, the tab icon, the name and one accent colour. Everything else
 * stays derived, so a wrong answer here cannot make the interface
 * unreadable.
 *
 * Values live in Kanboard's own `settings` table, so they survive a plugin
 * upgrade; the two images live in the data directory next to every other
 * upload, because the plugin folder is replaced wholesale on upgrade and is
 * not writable on a sane install anyway.
 */
class BrandingModel extends Base
{
    /**
     * The two images. Also the value of the `image` URL parameter, so they
     * are validated against this list before anything touches the disk.
     */
    const LOGO = 'logo';
    const FAVICON = 'favicon';

    /**
     * A mark is a small file. The cap is here to keep someone from parking a
     * 12 MB photograph in the sidebar, not because the storage cares.
     */
    const MAX_UPLOAD_SIZE = 1048576;

    /**
     * The defaults are the theme as it ships: the blue that tokens.css
     * carries, and the bundled mark. An empty setting means "the default",
     * so removing a custom value is the same operation as never setting one.
     */
    const DEFAULT_COLOR = '#1145af';
    const DEFAULT_SECONDARY = '#f7f7f7';

    /**
     * The two palettes, named. Kanboard's own Theme preference decides which
     * one a given request is in — this plugin never picks for the user.
     */
    const LIGHT = 'light';
    const DARK = 'dark';
    /**
     * The name an instance carries before anyone has answered. Kanboard's
     * own, because a theme should not put its author's name on someone
     * else's board — an instance that wants a different one says so in
     * Settings → Appearance.
     */
    const DEFAULT_TITLE = 'Kanboard';

    /**
     * How much of the lockup is drawn.
     *
     * A mark that already contains its own wordmark does not want the name
     * printed beside it, and an instance whose name is the point does not
     * want a tagline under it. Three answers cover every logo anyone has:
     * the mark alone, the mark and the name, or all three.
     */
    const DISPLAY_MARK = 'mark';
    const DISPLAY_TITLE = 'title';
    const DISPLAY_FULL = 'full';
    const DEFAULT_DISPLAY = self::DISPLAY_FULL;

    /**
     * The mark's size, as a percentage of the size the theme draws it at.
     * Bounded rather than free: a logo is one element of a row that also
     * holds a name, and outside this range it stops being that.
     */
    const DEFAULT_SCALE = 100;
    const MIN_SCALE = 50;
    const MAX_SCALE = 200;

    /**
     * What each slot accepts, and the type it is served back as.
     *
     * No .ico. A favicon has to out-rank the SVG one Kanboard declares a few
     * lines above ours, and the only way to do that is to be an SVG too — a
     * raster upload is served wrapped in one (see getFaviconSvg). An ICO
     * cannot be wrapped, so Firefox would keep showing Kanboard's icon and
     * the setting would look broken. PNG and SVG are what a favicon is now.
     */
    private static $formats = array(
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
    );

    private static $slots = array(
        self::LOGO    => array('png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'),
        self::FAVICON => array('png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'),
    );

    /**
     * The stored image, or an empty array when the slot is untouched.
     *
     * `hash` is part of every URL the image is served under, which is what
     * makes replacing a logo take effect immediately — for the browser, and
     * for Gmail, which caches a mail image by URL and never asks again.
     */
    public function getImage($type)
    {
        $path = $this->configModel->get($this->key($type, 'path'), '');

        if ($path === '') {
            return array();
        }

        return array(
            'path' => $path,
            'mime' => $this->configModel->get($this->key($type, 'mime'), 'image/png'),
            'hash' => $this->configModel->get($this->key($type, 'hash'), ''),
        );
    }

    public function hasImage($type)
    {
        return $this->getImage($type) !== array();
    }

    /**
     * True when the slot holds something a mail client or an apple-touch-icon
     * can actually draw. Both fall back to the bundled PNG otherwise.
     */
    public function hasRasterImage($type)
    {
        $image = $this->getImage($type);

        return $image !== array() && $image['mime'] !== 'image/svg+xml';
    }

    public function getBlob($type)
    {
        $image = $this->getImage($type);

        if ($image === array()) {
            return '';
        }

        return $this->objectStorage->get($image['path']);
    }

    /**
     * Validate an upload and take it. Throws with a sentence the settings
     * screen can show as-is; the caller turns that into a flash message.
     */
    public function upload($type, array $file)
    {
        if (! isset(self::$slots[$type])) {
            throw new RuntimeException(t('Unknown image.'));
        }

        if (empty($file['name']) || ! isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(t('The file could not be uploaded.'));
        }

        if ($file['size'] <= 0) {
            throw new RuntimeException(t('The file could not be uploaded.'));
        }

        if ($file['size'] > self::MAX_UPLOAD_SIZE) {
            throw new RuntimeException(t('The image must be smaller than %d KB.', self::MAX_UPLOAD_SIZE / 1024));
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (! in_array($extension, self::$slots[$type], true)) {
            throw new RuntimeException(t('Allowed formats: %s.', implode(', ', self::$slots[$type])));
        }

        $this->validateContent($extension, $file['tmp_name']);

        // A new name on every upload, so a replaced image can never be served
        // from a cache under the path the old one had.
        $key = 'shadcn/'.$type.'-'.substr(sha1(uniqid('', true)), 0, 12).'.'.$extension;

        if (! $this->objectStorage->moveUploadedFile($file['tmp_name'], $key)) {
            throw new RuntimeException(t('Unable to upload files, check the permissions of your data folder.'));
        }

        $previous = $this->getImage($type);

        $this->configModel->save(array(
            $this->key($type, 'path') => $key,
            $this->key($type, 'mime') => self::$formats[$extension],
            $this->key($type, 'hash') => substr(sha1($key), 0, 16),
        ));

        $this->discard($previous);

        return true;
    }

    /**
     * Back to the bundled image. The setting is emptied rather than removed,
     * so the row keeps its audit trail of who changed it and when.
     */
    public function removeImage($type)
    {
        $previous = $this->getImage($type);

        $this->configModel->save(array(
            $this->key($type, 'path') => '',
            $this->key($type, 'mime') => '',
            $this->key($type, 'hash') => '',
        ));

        $this->discard($previous);

        return true;
    }

    /**
     * The palette an instance is allowed to answer.
     *
     * Six of shadcn's own tokens, and only six: the two that carry the brand,
     * and the four surfaces the interface is actually built out of. The state
     * colours — success, warning, danger, info — are deliberately not here:
     * they mean something, and an instance that recolours "overdue" has not
     * branded itself, it has broken a signal.
     *
     * Each is answered twice — once for the light palette, once for the dark
     * — because a colour that reads well on white is not always the one that
     * reads well at night; the dark answer is optional and falls back to the
     * light one, which is what every instance had before there were two.
     *
     * The defaults are the theme's own values written in hex — one per
     * palette, because tokens.css and theme-dark.css do not agree about any
     * surface — so the swatch shows what the screen is actually drawing
     * rather than a placeholder.
     */
    private static $palette = array(
        'accent'    => array('setting' => 'shadcn_brand_color',     'default' => self::DEFAULT_COLOR,     'dark' => self::DEFAULT_COLOR),
        'secondary' => array('setting' => 'shadcn_brand_secondary', 'default' => self::DEFAULT_SECONDARY, 'dark' => '#434343'),
        'sidebar'   => array('setting' => 'shadcn_brand_sidebar',   'default' => '#ffffff',               'dark' => '#171717'),
        'muted'     => array('setting' => 'shadcn_brand_muted',     'default' => '#f5f5f5',               'dark' => '#434343'),
        'hover'     => array('setting' => 'shadcn_brand_hover',     'default' => '#f5f5f5',               'dark' => '#434343'),
        'border'    => array('setting' => 'shadcn_brand_border',    'default' => '#e5e5e5',               'dark' => '#2b2b2b'),
    );

    public static function getPalette()
    {
        return self::$palette;
    }

    public static function getDefaultColor($key, $scheme = self::LIGHT)
    {
        if (! isset(self::$palette[$key])) {
            return self::DEFAULT_COLOR;
        }

        return $scheme === self::DARK ? self::$palette[$key]['dark'] : self::$palette[$key]['default'];
    }

    /**
     * `$scheme` is 'light' or 'dark'; anything else is treated as light,
     * because a wrong scheme should give a colour rather than an error.
     */
    public function getBrandColor($key, $scheme = self::LIGHT)
    {
        if (! isset(self::$palette[$key])) {
            return self::DEFAULT_COLOR;
        }

        $value = $this->storedColor($key, $scheme);

        /* A dark field left empty means "the same colour at night", which is
         * only true once a light one has been answered. With neither
         * answered the palette is the theme's own, and at night that is a
         * different colour from the one the light row shows. */
        if ($value === '' && $scheme === self::DARK) {
            $value = $this->storedColor($key, self::LIGHT);
        }

        return $value ?: self::getDefaultColor($key, $scheme);
    }

    public function hasBrandColor($key, $scheme = self::LIGHT)
    {
        return $this->storedColor($key, $scheme) !== '';
    }

    /** The accent, and the quiet colour, by the names the templates use. */
    public function getColor($scheme = self::LIGHT)
    {
        return $this->getBrandColor('accent', $scheme);
    }

    public function hasCustomColor($scheme = self::LIGHT)
    {
        return $this->hasBrandColor('accent', $scheme);
    }

    /**
     * The quiet colour. Not a second brand colour competing with the first:
     * it is the plate under the controls that are not the page's action —
     * the search pill and the bell in the top bar, and the chips inside a
     * multi-select. Left at the default it is the near-white shadcn ships,
     * which is why an untouched instance shows no colour there at all.
     */
    public function getSecondaryColor($scheme = self::LIGHT)
    {
        return $this->getBrandColor('secondary', $scheme);
    }

    public function hasCustomSecondaryColor($scheme = self::LIGHT)
    {
        return $this->hasBrandColor('secondary', $scheme);
    }

    private function storedColor($key, $scheme)
    {
        return $this->normalizeColor($this->configModel->get($this->colorKey($key, $scheme), ''));
    }

    /**
     * The settings row a colour lives in. Light keeps the original name so
     * an instance that answered before there were two palettes keeps its
     * answer.
     */
    public function colorKey($key, $scheme)
    {
        $setting = isset(self::$palette[$key]) ? self::$palette[$key]['setting'] : $key;

        return $scheme === self::DARK ? $setting.'_dark' : $setting;
    }

    public function getTitle()
    {
        return $this->configModel->get('shadcn_brand_title', self::DEFAULT_TITLE);
    }

    /**
     * Which of the three lockups this instance draws. An unknown value —
     * a hand-edited settings row, a downgrade — reads as the default rather
     * than as a broken sidebar.
     */
    public function getDisplay()
    {
        $display = $this->configModel->get('shadcn_brand_display', self::DEFAULT_DISPLAY);

        return in_array($display, self::getDisplayModes(), true) ? $display : self::DEFAULT_DISPLAY;
    }

    public static function getDisplayModes()
    {
        return array(self::DISPLAY_MARK, self::DISPLAY_TITLE, self::DISPLAY_FULL);
    }

    /**
     * The mark's size as a percentage. Out-of-range values are pulled back
     * to the nearest end rather than refused: the setting arrives from a
     * slider, and a number outside its own range is not worth an error.
     */
    public function getLogoScale()
    {
        $scale = (int) $this->configModel->get('shadcn_brand_logo_scale', self::DEFAULT_SCALE);

        return max(self::MIN_SCALE, min(self::MAX_SCALE, $scale));
    }

    public function hasCustomLogoScale()
    {
        return $this->getLogoScale() !== self::DEFAULT_SCALE;
    }

    /**
     * A percentage the settings screen can save, or an empty string for
     * "leave it at the default" — the same contract normalizeColor has.
     */
    public function normalizeScale($value)
    {
        $value = trim((string) $value);

        if ($value === '' || ! ctype_digit(ltrim($value, '-'))) {
            return '';
        }

        $scale = max(self::MIN_SCALE, min(self::MAX_SCALE, (int) $value));

        return $scale === self::DEFAULT_SCALE ? '' : (string) $scale;
    }

    public function getSubtitle()
    {
        $subtitle = $this->configModel->get('shadcn_brand_subtitle', '');

        return $subtitle !== '' ? $subtitle : t('My project management tool');
    }

    /**
     * `#abc`, `#AABBCC` and a bare `aabbcc` all mean the same thing to a
     * person typing one in. Anything else is not a colour, and an empty
     * string is how the caller learns to keep the default.
     */
    public function normalizeColor($value)
    {
        $value = ltrim(trim((string) $value), '#');

        if (preg_match('/^[0-9a-fA-F]{3}$/', $value)) {
            $value = $value[0].$value[0].$value[1].$value[1].$value[2].$value[2];
        }

        return preg_match('/^[0-9a-fA-F]{6}$/', $value) ? '#'.strtolower($value) : '';
    }

    /**
     * Black or white on top of the brand colour, whichever a reader can
     * actually see. WCAG relative luminance, then the larger of the two
     * contrast ratios — so a yellow brand gets dark text instead of the
     * white the theme would otherwise have hard-coded.
     */
    public function getForegroundColor($background)
    {
        $luminance = $this->relativeLuminance($background);

        $onWhite = 1.05 / ($luminance + 0.05);
        $onBlack = ($luminance + 0.05) / 0.05;

        return $onBlack >= $onWhite ? '#0a0a0a' : '#ffffff';
    }

    /**
     * How the colour fares as text on a white surface — the email body, the
     * one place the theme cannot hand off to a computed foreground. Below
     * 4.5 the caller keeps the default blue rather than shipping a link
     * nobody can read.
     */
    public function contrastWithWhite($hex)
    {
        return 1.05 / ($this->relativeLuminance($hex) + 0.05);
    }

    private function relativeLuminance($hex)
    {
        $hex = ltrim($hex, '#');
        $channels = array(
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        );

        foreach ($channels as $index => $channel) {
            $channels[$index] = $channel <= 0.03928
                ? $channel / 12.92
                : pow(($channel + 0.055) / 1.055, 2.4);
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    /**
     * An extension is a claim, not a fact.
     *
     * Rasters go through getimagesize(), which reads the header rather than
     * the name. SVG has no header to read, so it is checked for the one
     * thing that makes it dangerous — a document that can run script. The
     * image route serves it under `default-src 'none'` as well, so this is
     * the second of two locks rather than the only one.
     */
    private function validateContent($extension, $filename)
    {
        if ($extension === 'svg') {
            $content = (string) file_get_contents($filename, false, null, 0, 262144);

            if (stripos($content, '<svg') === false) {
                throw new RuntimeException(t('This file is not a valid image.'));
            }

            if (preg_match('/<script|javascript:|\son\w+\s*=/i', $content)) {
                throw new RuntimeException(t('This SVG contains script and was refused.'));
            }

            return;
        }

        if (@getimagesize($filename) === false) {
            throw new RuntimeException(t('This file is not a valid image.'));
        }
    }

    /**
     * Deleting the file the settings no longer point at. A failure here is
     * an orphan in the data directory, never a broken logo, so it is not
     * worth failing the save over.
     */
    private function discard(array $image)
    {
        if ($image === array()) {
            return;
        }

        try {
            $this->objectStorage->remove($image['path']);
        } catch (\Exception $e) {
            $this->logger->error('Shadcn: unable to remove '.$image['path'].': '.$e->getMessage());
        }
    }

    /**
     * An uploaded image, always as an SVG document.
     *
     * Kanboard declares an SVG icon of its own a few lines before the hook
     * this plugin renders into, and a browser handed both an SVG and a PNG
     * reaches for the SVG regardless of which came last. So a raster upload
     * is wrapped in a one-element SVG rather than declared as a PNG — same
     * pixels, but a document type that can win the tie.
     *
     * The wrapper is generated here, never taken from the upload, so the
     * only user content in it is a base64 payload inside an attribute.
     */
    public function getSvgImage($type)
    {
        $image = $this->getImage($type);

        if ($image === array()) {
            return '';
        }

        $blob = $this->objectStorage->get($image['path']);

        if ($image['mime'] === 'image/svg+xml') {
            return $blob;
        }

        $data = 'data:'.$image['mime'].';base64,'.base64_encode($blob);

        return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 64 64">'
            .'<image href="'.$data.'" xlink:href="'.$data.'" x="0" y="0" width="64" height="64" preserveAspectRatio="xMidYMid meet"/>'
            .'</svg>';
    }

    private function key($type, $suffix)
    {
        return 'shadcn_'.$type.'_'.$suffix;
    }
}
