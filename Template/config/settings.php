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
$scColor = $branding['has_custom_color'] ? $branding['color'] : '';
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

    <section class="sc-brand-card" data-sc-brand-color-card>
        <div class="sc-brand-card-head">
            <h3><?= t('Accent color') ?></h3>
            <p><?= t('One hex value, for example #1145af. Buttons, links, the active sidebar item and the email header all follow it.') ?></p>
        </div>

        <div class="sc-brand-card-body">
            <div class="sc-brand-color">
                <?php /* The swatch is a second view of the same value, kept in
                         step by branding.js. Only the text field is submitted,
                         so an instance without JavaScript loses the picker
                         and keeps the setting. */ ?>
                <input type="color" class="sc-brand-swatch" id="form-shadcn_brand_color_picker"
                       value="<?= $this->text->e($branding['color']) ?>"
                       aria-label="<?= t('Accent color') ?>" tabindex="-1">
                <input type="text" name="shadcn_brand_color" id="form-shadcn_brand_color"
                       class="sc-brand-hex"
                       value="<?= $this->text->e($scColor) ?>"
                       placeholder="<?= $this->text->e($branding['default_color']) ?>"
                       autocomplete="off" spellcheck="false" maxlength="7">
                <button type="button" class="btn sc-brand-reset" data-sc-brand-reset
                        data-default="<?= $this->text->e($branding['default_color']) ?>"><?= t('Reset') ?></button>
            </div>

            <p class="sc-brand-help">
                <?= t('Leave empty for the theme default.') ?>
                <?= t('Text on top of the accent is chosen automatically, whichever of black or white is readable.') ?>
            </p>

            <div class="sc-brand-preview" style="--sc-brand-preview: <?= $this->text->e($branding['color']) ?>; --sc-brand-preview-fg: <?= $this->text->e($branding['foreground']) ?>;">
                <span class="sc-brand-preview-label"><?= t('Preview') ?></span>
                <span class="sc-brand-preview-btn"><?= t('Save') ?></span>
                <span class="sc-brand-preview-badge"><?= t('Active') ?></span>
                <span class="sc-brand-preview-link"><?= t('A link') ?></span>
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
