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

    public function getColor()
    {
        return $this->brandingModel()->getColor();
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
    public function getThemeCss()
    {
        $model = $this->brandingModel();
        $rules = array();

        if ($model->hasCustomColor()) {
            $color = $model->getColor();
            $foreground = $model->getForegroundColor($color);

            $rules[] = '--primary: '.$color;
            $rules[] = '--primary-foreground: '.$foreground;
            $rules[] = '--sidebar-primary: '.$color;
            $rules[] = '--sidebar-primary-foreground: '.$foreground;
            $rules[] = '--badge-primary-foreground: '.$foreground;
        }

        if ($this->hasCustomLogo()) {
            $rules[] = '--sc-logo: url("'.$this->getLogoCssUrl().'")';
        }

        return $rules === array() ? '' : ':root {'.implode('; ', $rules).'}';
    }

    /**
     * The model, resolved once per request. Registered by Plugin.php, so
     * this is a lookup rather than a construction.
     */
    private function brandingModel()
    {
        return $this->container['shadcnBrandingModel'];
    }
}
