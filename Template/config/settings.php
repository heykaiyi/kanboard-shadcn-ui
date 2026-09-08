<?php
/**
 * Settings → Appearance.
 *
 * The instance's own identity, and deliberately only that: a name, a line
 * under it, one accent colour and the two images. Not a theme editor — the
 * rest of the palette stays derived, so a bad answer here cannot produce an
 * unreadable screen.
 *
 * The screen is the plugin's own markup rather than Kanboard's form helpers.
 * Every other settings page is a column of labels and inputs; this one is
 * about things you look at, so the value and its effect sit side by side —
 * shadcn Cards, a swatch beside the hex, and each image shown at the size it
 * is actually drawn.
 *
 * Assets/js/branding.js makes the previews live. Without it the screen still
 * works: the swatch is a second input on the same value, and the file inputs
 * are ordinary file inputs.
 */
$scTitle = isset($values['shadcn_brand_title']) ? $values['shadcn_brand_title'] : '';
$scSubtitle = isset($values['shadcn_brand_subtitle']) ? $values['shadcn_brand_subtitle'] : '';
?>
<div class="page-header">
    <h2><?= t('Appearance') ?></h2>
</div>

<form class="sc-brand" method="post" enctype="multipart/form-data"
      action="<?= $this->url->href('ConfigController', 'save', array('plugin' => 'Shadcn')) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <section class="sc-brand-card">
        <div class="sc-brand-card-head">
            <h3><?= t('Name') ?></h3>
            <p><?= t('Shown beside the mark in the sidebar, on the login screen and in every outgoing email.') ?></p>
        </div>

        <div class="sc-brand-card-body">
            <div class="sc-brand-field">
                <label for="form-shadcn_brand_title"><?= t('Application name') ?></label>
                <input type="text" name="shadcn_brand_title" id="form-shadcn_brand_title"
                       value="<?= $this->text->e($scTitle) ?>"
                       placeholder="<?= $this->text->e($branding['title']) ?>"
                       autocomplete="off">
            </div>

            <div class="sc-brand-field">
                <label for="form-shadcn_brand_subtitle"><?= t('Tagline') ?></label>
                <input type="text" name="shadcn_brand_subtitle" id="form-shadcn_brand_subtitle"
                       value="<?= $this->text->e($scSubtitle) ?>"
                       placeholder="<?= $this->text->e($branding['subtitle']) ?>"
                       autocomplete="off">
                <p class="sc-brand-help"><?= t('The small line under the name in the sidebar. Leave empty for the default.') ?></p>
            </div>
        </div>
    </section>

    <section class="sc-brand-card">
        <div class="sc-brand-card-head">
            <h3><?= t('Colors') ?></h3>
            <p><?= t('Two hex values: the one the interface acts with, and the one it rests on. Each of them can answer twice — once for the light palette, once for the dark.') ?></p>
        </div>

        <div class="sc-brand-card-body">
            <?php /* Each swatch is a second view of the field beside it, kept
                     in step by branding.js. Only the text field is submitted,
                     so an instance without JavaScript loses the picker and
                     keeps the setting. */ ?>
            <?php foreach ($branding['colors'] as $scKey => $scColorField): ?>
                <div class="sc-brand-field">
                    <label for="form-<?= $scColorField['schemes']['light']['name'] ?>"><?= $this->text->e($scColorField['label']) ?></label>

                    <div class="sc-brand-schemes">
                        <?php foreach (array('light' => t('Light'), 'dark' => t('Dark')) as $scScheme => $scSchemeLabel): ?>
                            <?php $scField = $scColorField['schemes'][$scScheme] ?>
                            <div class="sc-brand-scheme" data-sc-brand-color="<?= $scKey ?>" data-sc-brand-scheme="<?= $scScheme ?>">
                                <span class="sc-brand-scheme-label"><?= $this->text->e($scSchemeLabel) ?></span>

                                <div class="sc-brand-color">
                                    <input type="color" class="sc-brand-swatch"
                                           value="<?= $this->text->e($scField['resolved']) ?>"
                                           aria-label="<?= $this->text->e($scColorField['label'].' — '.$scSchemeLabel) ?>" tabindex="-1">
                                    <input type="text" name="<?= $scField['name'] ?>" id="form-<?= $scField['name'] ?>"
                                           class="sc-brand-hex"
                                           value="<?= $this->text->e($scField['value']) ?>"
                                           placeholder="<?= $this->text->e($scColorField['default']) ?>"
                                           autocomplete="off" spellcheck="false" maxlength="7">
                                    <button type="button" class="btn sc-brand-reset"
                                            data-default="<?= $this->text->e($scColorField['default']) ?>"><?= t('Reset') ?></button>
                                </div>
                            </div>
                        <?php endforeach ?>
                    </div>

                    <p class="sc-brand-help"><?= $this->text->e($scColorField['help']) ?></p>
                </div>
            <?php endforeach ?>

            <p class="sc-brand-help">
                <?= t('Leave a field empty for the theme default; leave a dark field empty and the light colour is used at night too.') ?>
                <?= t('Text on top of a colour is chosen automatically, whichever of black or white is readable.') ?>
                <?= t('Which palette a person sees is their own Theme preference — this changes what each one looks like, not which one they get.') ?>
            </p>

            <div class="sc-brand-previews">
                <?php foreach (array('light' => t('Light'), 'dark' => t('Dark')) as $scScheme => $scSchemeLabel): ?>
                    <div class="sc-brand-preview sc-brand-preview-<?= $scScheme ?>"
                         data-sc-brand-preview-scheme="<?= $scScheme ?>"
                         style="--sc-brand-accent: <?= $this->text->e($branding['colors']['accent']['schemes'][$scScheme]['resolved']) ?>; --sc-brand-accent-fg: <?= $this->text->e($branding['colors']['accent']['schemes'][$scScheme]['foreground']) ?>; --sc-brand-secondary: <?= $this->text->e($branding['colors']['secondary']['schemes'][$scScheme]['resolved']) ?>; --sc-brand-secondary-fg: <?= $this->text->e($branding['colors']['secondary']['schemes'][$scScheme]['foreground']) ?>;">
                        <span class="sc-brand-preview-label"><?= $this->text->e($scSchemeLabel) ?></span>
                        <span class="sc-brand-preview-btn"><?= t('Save') ?></span>
                        <span class="sc-brand-preview-btn-2"><?= t('cancel') ?></span>
                        <span class="sc-brand-preview-badge"><?= t('Active') ?></span>
                        <span class="sc-brand-preview-link"><?= t('A link') ?></span>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <section class="sc-brand-card">
        <div class="sc-brand-card-head">
            <h3><?= t('Logo') ?></h3>
            <p><?= t('PNG, JPG, GIF, WebP or SVG, up to %d KB. A square image, since it is drawn in a square plate.', $branding['max_size'] / 1024) ?></p>
        </div>

        <div class="sc-brand-card-body">
            <div class="sc-brand-media">
                <span class="sc-brand-plate">
                    <img src="<?= $this->text->e($branding['logo_url']) ?>" alt="" data-sc-brand-preview="logo_file">
                </span>

                <div class="sc-brand-media-body">
                    <label class="sc-brand-file">
                        <span><?= t('Choose an image') ?></span>
                        <input type="file" name="logo_file" id="form-logo_file"
                               accept=".png,.jpg,.jpeg,.gif,.webp,.svg"
                               data-sc-brand-file="logo_file">
                    </label>
                    <span class="sc-brand-file-name" data-sc-brand-name="logo_file"><?= t('No file selected') ?></span>

                    <p class="sc-brand-help"><?= t('An SVG is used everywhere except in email, which no client renders it in — the bundled PNG stands in there.') ?></p>

                    <?php if ($branding['has_logo']): ?>
                        <?= $this->url->link(t('Restore the default logo'), 'ConfigController', 'remove', array('plugin' => 'Shadcn', 'image' => 'logo'), true, 'btn sc-brand-restore') ?>
                    <?php endif ?>
                </div>
            </div>

            <?php /* How much of the lockup is drawn, and how large the mark
                     is drawn in it. Both preview on the real sidebar as they
                     are changed, which is the whole reason they sit here
                     rather than in a column of labelled inputs. */ ?>
            <div class="sc-brand-lockup">
                <fieldset class="sc-brand-choice">
                    <legend><?= t('Lockup') ?></legend>

                    <?php foreach (array(
                        'mark'  => t('Mark only'),
                        'title' => t('Mark and name'),
                        'full'  => t('Mark, name and tagline'),
                    ) as $scValue => $scLabel): ?>
                        <label class="sc-brand-radio">
                            <input type="radio" name="shadcn_brand_display" value="<?= $scValue ?>"
                                   data-sc-brand-display
                                   <?= $branding['display'] === $scValue ? 'checked' : '' ?>>
                            <span><?= $this->text->e($scLabel) ?></span>
                        </label>
                    <?php endforeach ?>

                    <p class="sc-brand-help"><?= t('Applies to the sidebar, the phone top bar and the login screen. Email always carries the name, whatever is chosen here.') ?></p>
                </fieldset>

                <div class="sc-brand-field sc-brand-scale">
                    <label for="form-shadcn_brand_logo_scale">
                        <?= t('Mark size') ?>
                        <output for="form-shadcn_brand_logo_scale" data-sc-brand-scale-output><?= (int) $branding['scale'] ?>%</output>
                    </label>
                    <input type="range" name="shadcn_brand_logo_scale" id="form-shadcn_brand_logo_scale"
                           min="<?= (int) $branding['min_scale'] ?>" max="<?= (int) $branding['max_scale'] ?>" step="5"
                           value="<?= (int) $branding['scale'] ?>"
                           data-sc-brand-scale data-default="<?= (int) $branding['default_scale'] ?>">
                    <p class="sc-brand-help"><?= t('A percentage of the size the theme draws the mark at. The sidebar beside this screen follows the slider.') ?></p>
                </div>
            </div>
        </div>
    </section>

    <section class="sc-brand-card">
        <div class="sc-brand-card-head">
            <h3><?= t('Favicon') ?></h3>
            <p><?= t('PNG, JPG, GIF, WebP or SVG, up to %d KB. Square, and legible at 16 pixels.', $branding['max_size'] / 1024) ?></p>
        </div>

        <div class="sc-brand-card-body">
            <div class="sc-brand-media">
                <span class="sc-brand-plate sc-brand-plate-small">
                    <img src="<?= $this->text->e($branding['favicon_url'] !== '' ? $branding['favicon_url'] : $this->url->dir().'plugins/Shadcn/Assets/img/logo.svg') ?>" alt="" data-sc-brand-preview="favicon_file">
                </span>

                <div class="sc-brand-media-body">
                    <label class="sc-brand-file">
                        <span><?= t('Choose an image') ?></span>
                        <input type="file" name="favicon_file" id="form-favicon_file"
                               accept=".png,.jpg,.jpeg,.gif,.webp,.svg"
                               data-sc-brand-file="favicon_file">
                    </label>
                    <span class="sc-brand-file-name" data-sc-brand-name="favicon_file"><?= t('No file selected') ?></span>

                    <p class="sc-brand-help"><?= t('Leave this empty and the logo is used as the tab icon.') ?></p>

                    <?php if ($branding['has_favicon']): ?>
                        <?= $this->url->link(t('Restore the default favicon'), 'ConfigController', 'remove', array('plugin' => 'Shadcn', 'image' => 'favicon'), true, 'btn sc-brand-restore') ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </section>

    <div class="sc-brand-actions">
        <button type="submit" class="btn btn-blue"><?= t('Save') ?></button>
    </div>
</form>
