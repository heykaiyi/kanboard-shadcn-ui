<?php

namespace Kanboard\Plugin\Shadcn\Helper;

use Kanboard\Core\Base;
use Kanboard\Plugin\Shadcn\Model\BrandingModel;

/**
 * The branding, as the templates need it.
 *
 * BrandingModel owns the values; this turns them into the four shapes a
 * template actually asks for — a URL for a <link>, a URL for a CSS url(),
 * an absolute URL for an email, and the block of custom properties that
 * re-points the theme at the instance's own colour.
 *
 * Nothing here is conditional on a user: the branding is the same for
 * everyone, including anonymous visitors on the login screen.
 */
class BrandHelper extends Base
{
    /**
     * The bundled fallbacks. Every one of these is what the theme showed
     * before any of this was configurable, so an untouched instance renders
     * byte for byte what it did before.
     */
    const DEFAULT_MARK = 'plugins/Shadcn/Assets/img/logo-180.png';

    public function getTitle()
    {
        return $this->brandingModel()->getTitle();
    }

    public function getSubtitle()
    {
        return $this->brandingModel()->getSubtitle();
    }

    public function hasCustomLogo()
    {
        return $this->brandingModel()->hasImage(BrandingModel::LOGO);
    }

    /**
     * The mark, for a CSS url().
     *
     * tokens.css writes --sc-logo relative to itself, which only works
     * because it is a stylesheet. The override is an inline <style> in the
     * document, so this has to be root-relative instead — and built with
     * to() rather than href(), because a <style> element is raw text and
     * would render an &amp; literally.
     */
    public function getLogoCssUrl()
    {
        $image = $this->brandingModel()->getImage(BrandingModel::LOGO);

        if ($image === array()) {
            return $this->helper->url->dir().self::DEFAULT_MARK;
        }

        return $this->helper->url->to('BrandingController', 'image', array(
            'plugin' => 'Shadcn',
            'image'  => BrandingModel::LOGO,
            'hash'   => $image['hash'],
        ));
    }

    /**
     * The mark for an outgoing email: absolute, because there is no page to
     * be relative to, and never an SVG, because no mail client draws one.
     * An SVG upload therefore falls back to the bundled PNG rather than to
     * a broken image icon in everyone's inbox.
     *
     * Empty when the instance has no application URL configured, which is
     * the same condition the mail layout already hides the mark under.
     */
    public function getMailLogoUrl()
    {
        $base = $this->configModel->get('application_url', '');

        if ($base === '') {
            return '';
        }

        if (! $this->brandingModel()->hasRasterImage(BrandingModel::LOGO)) {
            return rtrim($base, '/').'/'.self::DEFAULT_MARK;
        }

        $image = $this->brandingModel()->getImage(BrandingModel::LOGO);

        // to() with $absolute, so the URL is built on application_url rather
        // than on the request's own directory — and with a raw & rather than
        // &amp;, because the mail layout escapes what it is handed.
        return $this->helper->url->to('BrandingController', 'image', array(
            'plugin' => 'Shadcn',
            'image'  => BrandingModel::LOGO,
            'hash'   => $image['hash'],
        ), '', true);
    }

    /**
     * Which upload the tab icon is drawn from.
     *
     * Its own, if one was uploaded. Otherwise the logo — an instance that
     * has set a mark and left the favicon alone means the mark, and saying
     * so here is cheaper than making everyone upload the same file twice.
     * Empty when neither is set, and the bundled icons stay.
     */
    public function getIconSlot()
    {
        foreach (array(BrandingModel::FAVICON, BrandingModel::LOGO) as $type) {
            if ($this->brandingModel()->hasImage($type)) {
                return $type;
            }
        }

        return '';
    }

    public function hasCustomIcon()
    {
        return $this->getIconSlot() !== '';
    }

    /**
     * Whether the tab icon can also serve as the apple-touch-icon, which
     * ignores SVG and would otherwise show nothing.
     */
    public function hasRasterIcon()
    {
        $slot = $this->getIconSlot();

        return $slot !== '' && $this->brandingModel()->hasRasterImage($slot);
    }

    /**
     * A tab icon URL, for an href attribute — so href() and its &amp;.
     *
     * `format` picks between the file as uploaded and the SVG wrapper the
     * model generates around a raster; the wrapper is what actually beats
     * Kanboard's own icon, the raw file is for apple-touch-icon.
     */
    public function getIconUrl($format = 'svg')
    {
        $slot = $this->getIconSlot();

        if ($slot === '') {
            return '';
        }

        $image = $this->brandingModel()->getImage($slot);

        return $this->helper->url->href('BrandingController', 'image', array(
            'plugin' => 'Shadcn',
            'image'  => $slot,
            'format' => $format,
            'hash'   => $image['hash'],
        ));
    }

    public function getColor($scheme = BrandingModel::LIGHT)
    {
        return $this->brandingModel()->getColor($scheme);
    }

    public function getSecondaryColor($scheme = BrandingModel::LIGHT)
    {
        return $this->brandingModel()->getSecondaryColor($scheme);
    }

    public function getDisplay()
    {
        return $this->brandingModel()->getDisplay();
    }

    public function getLogoScale()
    {
        return $this->brandingModel()->getLogoScale();
    }

    public function getForegroundColor()
    {
        return $this->brandingModel()->getForegroundColor($this->getColor());
    }

    /**
     * The accent as body text on white — the email, where there is no
     * computed foreground to lean on. A pale brand colour would be an
     * unreadable link, so below the WCAG AA threshold the default stands.
     */
    public function getLinkColor()
    {
        $color = $this->getColor();

        return $this->brandingModel()->contrastWithWhite($color) >= 4.5 ? $color : BrandingModel::DEFAULT_COLOR;
    }

    /**
     * The overrides, as a stylesheet fragment.
     *
     * Emitted after theme-dark.css / theme-auto.css, which is the only place
     * it can win in both palettes: those files restate --primary for the dark
     * theme, so anything declared before them is thrown away at night.
     *
     * Only the tokens that carry the brand are touched. --primary alone would
     * leave the sidebar's active item and the badge fills on the old blue,
     * because tokens.css gave those their own names.
     */
    public function getThemeCss($theme = BrandingModel::LIGHT)
    {
        $model = $this->brandingModel();
        $rules = $this->getColorRules(BrandingModel::LIGHT);

        if ($this->hasCustomLogo()) {
            $rules[] = '--sc-logo: url("'.$this->getLogoCssUrl().'")';
        }

        if ($model->hasCustomLogoScale()) {
            // A multiplier rather than a length: every mark in the theme is
            // sized off its own base — 2rem in the sidebar, 2.25rem on the
            // login screen — and one number has to move all of them without
            // flattening them to the same size.
            $rules[] = '--sc-brand-scale: '.number_format($model->getLogoScale() / 100, 2, '.', '');
        }

        $css = $rules === array() ? '' : ':root {'.implode('; ', $rules).'}';

        return $css.$this->getDarkCss($theme).$this->getDisplayCss();
    }

    /**
     * The night answer, gated the way the theme's own dark tokens are.
     *
     * Kanboard's Theme preference is per user and already decides which of
     * light / dark / auto this request is; the template hands that answer
     * here so the dark colours are emitted exactly where theme-dark.css and
     * theme-auto.css put theirs — flat for "Dark", behind
     * prefers-color-scheme for "Auto", and not at all for "Light".
     *
     * Only the colours that were answered separately are emitted. A dark
     * field left empty means "the same colour at night", which is what the
     * block above already says.
     */
    private function getDarkCss($theme)
    {
        if ($theme !== BrandingModel::DARK && $theme !== 'auto') {
            return '';
        }

        $rules = $this->getColorRules(BrandingModel::DARK);

        if ($rules === array()) {
            return '';
        }

        $block = ':root {'.implode('; ', $rules).'}';

        return $theme === BrandingModel::DARK ? $block : '@media (prefers-color-scheme: dark){'.$block.'}';
    }

    /**
     * Which custom properties each colour writes.
     *
     * --primary alone would leave the sidebar's active item and the badge
     * fills on the old blue, because tokens.css gave those their own names;
     * the same is true of every state colour and its badge. `%s` is the
     * colour, `%f` the foreground computed for it.
     */
    private static $tokens = array(
        'accent' => array(
            '--primary: %s', '--primary-foreground: %f',
            '--sidebar-primary: %s', '--sidebar-primary-foreground: %f',
            '--badge-primary-foreground: %f',
        ),
        'secondary' => array('--secondary: %s', '--secondary-foreground: %f'),
        'sidebar' => array('--sidebar: %s', '--sidebar-foreground: %f'),
        /* The quiet surface: table-list headers, the neutral chip, and the
         * wash --surface-subtle is mixed from. */
        'muted' => array(
            '--muted: %s', '--muted-foreground: %f',
            '--surface-neutral: %s', '--badge-neutral: %s',
        ),
        /* Where the pointer is: menu items, rows, outline buttons. */
        'hover' => array(
            '--accent: %s', '--accent-foreground: %f',
            '--sidebar-accent: %s', '--sidebar-accent-foreground: %f',
        ),
        /* Every hairline in the interface, including the one a field draws
         * around itself. No foreground: nothing is written on a border. */
        'border' => array(
            '--border: %s', '--input: %s', '--sidebar-border: %s',
        ),
    );

    private function getColorRules($scheme)
    {
        $model = $this->brandingModel();
        $rules = array();

        foreach (array_keys(BrandingModel::getPalette()) as $key) {
            if (! $model->hasBrandColor($key, $scheme)) {
                continue;
            }

            $color = $model->getBrandColor($key, $scheme);
            $foreground = $model->getForegroundColor($color);

            foreach (self::$tokens[$key] as $rule) {
                $rules[] = str_replace(array('%s', '%f'), array($color, $foreground), $rule);
            }
        }

        return $rules;
    }

    /**
     * The lockup, as the rules that take parts of it away.
     *
     * The three spans are always rendered — the brand links carry an
     * aria-label, so hiding the text costs nothing a reader needs — which
     * means the choice is one stylesheet fragment rather than a branch in
     * four templates. It is also what lets the settings screen preview the
     * change on the real sidebar as the radio is clicked.
     */
    public function getDisplayCss()
    {
        switch ($this->getDisplay()) {
            case BrandingModel::DISPLAY_MARK:
                return self::MARK_ONLY_CSS;
            case BrandingModel::DISPLAY_TITLE:
                return '.sc-sb-brand-sub {display: none}';
            default:
                return '';
        }
    }

    /**
     * Mark only: the mark stops being a 2rem square.
     *
     * Beside a name it is one element of a row and a square is right. Alone
     * it *is* the lockup, so it takes the row — a wordmark four times wider
     * than it is tall was being letterboxed into 32px and came out
     * unreadable. `contain` keeps the proportions whatever shape the file
     * is: a wide logo fills the width, a square one fills the height.
     *
     * The collapsed rail is the exception. There is no width to give it
     * there, so the square comes back — two attributes deep, which is what
     * out-ranks the rules above without !important.
     *
     * Kept as CSS rather than a class on the element because the whole
     * lockup setting is one stylesheet fragment: no JavaScript is needed
     * for it to work, and Settings → Appearance previews it by swapping
     * this same text.
     */
    const MARK_ONLY_CSS = '.sc-sb-brand-text, .sc-topbar-brand-name, .sc-auth-brand-title {display: none}'
        .'.sc-sb-brand-mark {flex: 1 1 auto; width: auto; height: calc(2.75rem * var(--sc-brand-scale, 1)); background-position: left center}'
        .'.sc-topbar-brand-mark {flex: 1 1 auto; width: auto; min-width: 5rem; height: calc(2.25rem * var(--sc-brand-scale, 1)); background-position: left center}'
        .'.sc-auth-brand-mark {width: 13rem; max-width: 100%; height: calc(2.75rem * var(--sc-brand-scale, 1)); background-position: left center}'
        .'html[data-sc-sidebar="collapsed"] .sc-sb-brand-mark {flex: 0 0 auto; width: calc(2rem * var(--sc-brand-scale, 1)); height: calc(2rem * var(--sc-brand-scale, 1)); background-position: center}';

    /**
     * The model, resolved once per request. Registered by Plugin.php, so
     * this is a lookup rather than a construction.
     */
    private function brandingModel()
    {
        return $this->container['shadcnBrandingModel'];
    }
}
