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
                'colors' => $this->getColorFields(),
                'display' => $this->shadcnBrandingModel->getDisplay(),
                'scale' => $this->shadcnBrandingModel->getLogoScale(),
                'min_scale' => BrandingModel::MIN_SCALE,
                'max_scale' => BrandingModel::MAX_SCALE,
                'default_scale' => BrandingModel::DEFAULT_SCALE,
                'logo_url' => $this->helper->shadcnBrand->getLogoCssUrl(),
                'has_logo' => $this->shadcnBrandingModel->hasImage(BrandingModel::LOGO),
                'favicon_url' => $this->helper->shadcnBrand->getIconUrl(),
                'has_favicon' => $this->shadcnBrandingModel->hasImage(BrandingModel::FAVICON),
                'max_size' => BrandingModel::MAX_UPLOAD_SIZE,
            ),
        )));
    }

    /**
     * The palette, as the screen needs it: every colour answered once for
     * the light palette and once for the dark. The resolved value is what
     * the swatch shows — a dark field left empty shows the light colour,
     * which is the colour it will actually be.
     */
    private function getColorFields()
    {
        $model = $this->shadcnBrandingModel;

        $copy = array(
            'accent' => array(
                'label' => t('Accent color'),
                'help' => t('Buttons, links, the active sidebar item, focus borders and the email header all follow it.'),
            ),
            'secondary' => array(
                'label' => t('Secondary color'),
                'help' => t('The plate under the quiet controls: the search box and the bell in the top bar, and the chips inside a multi-select.'),
            ),
            'sidebar' => array(
                'label' => t('Sidebar background'),
                'help' => t('The sidebar\'s own surface, behind the navigation.'),
            ),
            'muted' => array(
                'label' => t('Muted surface'),
                'help' => t('The quiet fill: list headers, neutral chips, and the cards a message or an activity entry sits on.'),
            ),
            'hover' => array(
                'label' => t('Hover surface'),
                'help' => t('Where the pointer is: menu items, rows, and outline buttons.'),
            ),
            'border' => array(
                'label' => t('Border'),
                'help' => t('Every hairline in the interface, including the one a field draws around itself.'),
            ),
        );

        $colors = array();

        foreach (BrandingModel::getPalette() as $key => $definition) {
            $colors[$key] = $copy[$key];
            $colors[$key]['default'] = $definition['default'];

            foreach (array(BrandingModel::LIGHT, BrandingModel::DARK) as $scheme) {
                $value = $model->getBrandColor($key, $scheme);

                $colors[$key]['schemes'][$scheme] = array(
                    'name' => $model->colorKey($key, $scheme),
                    'value' => $model->hasBrandColor($key, $scheme) ? $value : '',
                    'resolved' => $value,
                    'foreground' => $model->getForegroundColor($value),
                    // Each palette's own default, so an empty dark field
                    // offers the night colour rather than the day one.
                    'placeholder' => BrandingModel::getDefaultColor($key, $scheme),
                );
            }
        }

        return $colors;
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

        $colors = array();

        foreach (array_keys(BrandingModel::getPalette()) as $key) {
            foreach (array(BrandingModel::LIGHT, BrandingModel::DARK) as $scheme) {
                $field = $this->shadcnBrandingModel->colorKey($key, $scheme);
                $raw = isset($values[$field]) ? $values[$field] : '';
                $colors[$field] = $this->shadcnBrandingModel->normalizeColor($raw);

                if ($colors[$field] === '' && ! empty($raw)) {
                    $this->flash->failure(t('That is not a valid colour. Use a hex value such as #1145af.'));
                    $this->redirectToSettings();
                    return;
                }
            }
        }

        // Both of these come from controls with a fixed set of answers, so
        // anything else is a forged post rather than a mistake to report:
        // the value simply falls back to the default.
        $display = isset($values['shadcn_brand_display']) ? $values['shadcn_brand_display'] : '';

        if (! in_array($display, BrandingModel::getDisplayModes(), true)) {
            $display = BrandingModel::DEFAULT_DISPLAY;
        }

        $saved = $this->configModel->save($colors + array(
            'shadcn_brand_title' => isset($values['shadcn_brand_title']) ? trim($values['shadcn_brand_title']) : '',
            'shadcn_brand_subtitle' => isset($values['shadcn_brand_subtitle']) ? trim($values['shadcn_brand_subtitle']) : '',
            'shadcn_brand_display' => $display,
            'shadcn_brand_logo_scale' => $this->shadcnBrandingModel->normalizeScale(
                isset($values['shadcn_brand_logo_scale']) ? $values['shadcn_brand_logo_scale'] : ''
            ),
        ));

        $failures = array();

        $labels = array(
            BrandingModel::LOGO => t('Logo'),
            BrandingModel::FAVICON => t('Favicon'),
        );

        foreach (array(BrandingModel::LOGO, BrandingModel::FAVICON) as $type) {
            $file = $this->request->getFileInfo($type.'_file');

            if (empty($file['name'])) {
                continue;
            }

            try {
                $this->shadcnBrandingModel->upload($type, $file);
            } catch (RuntimeException $e) {
                $failures[] = $labels[$type].'：'.$e->getMessage();
                $this->logRefusal($type, $file, $e->getMessage());
            } catch (\Exception $e) {
                $failures[] = $labels[$type].'：'.t('Unable to upload files, check the permissions of your data folder.');
                $this->logRefusal($type, $file, get_class($e).': '.$e->getMessage());
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
     * A refused upload, in the log as well as on the screen.
     *
     * The flash message is gone on the next click and says nothing about the
     * file that caused it. Two of the three reasons an upload is refused are
     * facts about that file — its name and its size — so they are written
     * down where they can still be read afterwards.
     */
    private function logRefusal($type, array $file, $reason)
    {
        $this->logger->error(sprintf(
            'Shadcn: %s upload refused — name=%s size=%s error=%s reason=%s',
            $type,
            isset($file['name']) ? $file['name'] : '(none)',
            isset($file['size']) ? $file['size'] : '(none)',
            isset($file['error']) ? $file['error'] : '(none)',
            $reason
        ));
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
