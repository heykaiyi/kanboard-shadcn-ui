<?php

namespace Kanboard\Plugin\Shadcn\Controller;

use Kanboard\Core\Controller\AccessForbiddenException;
use Kanboard\Plugin\Shadcn\Model\BrandingModel;
use RuntimeException;

/**
 * Settings → Appearance.
 *
 * Extends Kanboard's own ConfigController for one reason beyond the layout
 * helper: the application access map grants ConfigController to admins and
 * nobody else, and the router matches on the class's short name — so this
 * screen inherits that rule instead of restating it.
 */
class ConfigController extends \Kanboard\Controller\ConfigController
{
    public function show()
    {
        $this->response->html($this->helper->layout->config('shadcn:config/settings', array(
            'title' => t('Settings').' &gt; '.t('Appearance'),
            'branding' => array(
                'title' => $this->shadcnBrandingModel->getTitle(),
                'subtitle' => $this->shadcnBrandingModel->getSubtitle(),
                'color' => $this->shadcnBrandingModel->getColor(),
                'display' => $this->shadcnBrandingModel->getDisplay(),
                'scale' => $this->shadcnBrandingModel->getLogoScale(),
                'min_scale' => BrandingModel::MIN_SCALE,
                'max_scale' => BrandingModel::MAX_SCALE,
                'default_scale' => BrandingModel::DEFAULT_SCALE,
                'has_custom_color' => $this->shadcnBrandingModel->hasCustomColor(),
                'foreground' => $this->shadcnBrandingModel->getForegroundColor($this->shadcnBrandingModel->getColor()),
                'default_color' => BrandingModel::DEFAULT_COLOR,
                'logo_url' => $this->helper->shadcnBrand->getLogoCssUrl(),
                'has_logo' => $this->shadcnBrandingModel->hasImage(BrandingModel::LOGO),
                'favicon_url' => $this->helper->shadcnBrand->getIconUrl(),
                'has_favicon' => $this->shadcnBrandingModel->hasImage(BrandingModel::FAVICON),
                'max_size' => BrandingModel::MAX_UPLOAD_SIZE,
            ),
        )));
    }

    /**
     * One form, so one save: the text fields, then whichever of the two file
     * inputs was actually filled in. A rejected image does not roll the text
     * back — they are independent settings that happen to share a screen.
     */
    public function save()
    {
        $values = $this->request->getValues();

        // getValues() is where the CSRF token on a POST is checked, and it
        // answers a bad one with an empty array rather than an error. Left
        // alone that would read as "the admin cleared every field" and blank
        // the branding, so the emptiness is the check.
        if ($values === array()) {
            // One innocent way to arrive here: a file bigger than PHP's
            // post_max_size, which the server discards along with the rest of
            // the form — token included. The request body is gone but its
            // length was still announced, and that is the only thing that
            // separates it from a forged post.
            if (empty($_POST) && (int) $this->request->getServerVariable('CONTENT_LENGTH') > 0) {
                $this->flash->failure(t('The image must be smaller than %d KB.', BrandingModel::MAX_UPLOAD_SIZE / 1024));
                $this->redirectToSettings();
                return;
            }

            throw new AccessForbiddenException();
        }

        $color = $this->shadcnBrandingModel->normalizeColor(isset($values['shadcn_brand_color']) ? $values['shadcn_brand_color'] : '');

        if ($color === '' && ! empty($values['shadcn_brand_color'])) {
            $this->flash->failure(t('That is not a valid colour. Use a hex value such as #1145af.'));
            $this->redirectToSettings();
            return;
        }

        // Both of these come from controls with a fixed set of answers, so
        // anything else is a forged post rather than a mistake to report:
        // the value simply falls back to the default.
        $display = isset($values['shadcn_brand_display']) ? $values['shadcn_brand_display'] : '';

        if (! in_array($display, BrandingModel::getDisplayModes(), true)) {
            $display = BrandingModel::DEFAULT_DISPLAY;
        }

        $saved = $this->configModel->save(array(
            'shadcn_brand_title' => isset($values['shadcn_brand_title']) ? trim($values['shadcn_brand_title']) : '',
            'shadcn_brand_subtitle' => isset($values['shadcn_brand_subtitle']) ? trim($values['shadcn_brand_subtitle']) : '',
            'shadcn_brand_color' => $color,
            'shadcn_brand_display' => $display,
            'shadcn_brand_logo_scale' => $this->shadcnBrandingModel->normalizeScale(
                isset($values['shadcn_brand_logo_scale']) ? $values['shadcn_brand_logo_scale'] : ''
            ),
        ));

        $failures = array();

        foreach (array(BrandingModel::LOGO, BrandingModel::FAVICON) as $type) {
            $file = $this->request->getFileInfo($type.'_file');

            if (empty($file['name'])) {
                continue;
            }

            try {
                $this->shadcnBrandingModel->upload($type, $file);
            } catch (RuntimeException $e) {
                $failures[] = $e->getMessage();
            } catch (\Exception $e) {
                $this->logger->error('Shadcn: '.$e->getMessage());
                $failures[] = t('Unable to upload files, check the permissions of your data folder.');
            }
        }

        if ($failures !== array()) {
            $this->flash->failure(implode(' ', $failures));
        } elseif ($saved) {
            $this->flash->success(t('Settings saved successfully.'));
        } else {
            $this->flash->failure(t('Unable to save your settings.'));
        }

        $this->redirectToSettings();
    }

    /**
     * Back to the bundled image.
     */
    public function remove()
    {
        $this->checkCSRFParam();

        $type = $this->request->getStringParam('image');

        if (! in_array($type, array(BrandingModel::LOGO, BrandingModel::FAVICON), true)) {
            $this->redirectToSettings();
            return;
        }

        $this->shadcnBrandingModel->removeImage($type);
        $this->flash->success(t('Settings saved successfully.'));
        $this->redirectToSettings();
    }

    private function redirectToSettings()
    {
        $this->response->redirect($this->helper->url->to('ConfigController', 'show', array('plugin' => 'Shadcn')));
    }
}
