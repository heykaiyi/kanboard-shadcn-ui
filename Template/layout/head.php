<?php
/**
 * Per-user dark tokens, the tab icon, and the instance's own branding.
 *
 * Kanboard already loads light.min.css / dark.min.css / auto.min.css based on
 * the user's Theme preference, but a plugin stylesheet registered on
 * template:layout:css is a single static file for everybody. So the light
 * tokens ship unconditionally in tokens.css and the dark ones are picked here,
 * where the request — and therefore the user — is known.
 *
 * getTheme() falls back to 'light' for anonymous visitors (public boards,
 * login screen), which is what we want.
 */
$shadcn_theme = $this->user->getTheme();
$shadcn_brand_css = $this->shadcnBrand->getThemeCss($shadcn_theme);
?>
<?php /* Noto Sans TC is not on most machines, so the stack alone would quietly
       * fall through to PingFang TC or Microsoft JhengHei. Google serves it
       * with unicode-range subsetting, so a browser only fetches the ranges
       * the page actually uses. Plugin.php widens style-src / font-src to
       * match. */ ?>
<?php /* Kanboard declares its own icons a few lines above this hook, and
       * an SVG is what a browser reaches for first — so ours is an SVG too,
       * declared later, which is what makes it win. A custom icon is served
       * as an SVG for the same reason, wrapped by BrandingController when
       * what was uploaded is a raster. The PNGs are for the clients that
       * ignore SVG icons entirely. */ ?>
<?php if ($this->shadcnBrand->hasCustomIcon()): ?>
    <link rel="icon" type="image/svg+xml" href="<?= $this->shadcnBrand->getIconUrl('svg') ?>">
    <?php if ($this->shadcnBrand->hasRasterIcon()): ?>
        <link rel="apple-touch-icon" href="<?= $this->shadcnBrand->getIconUrl('raw') ?>">
    <?php else: ?>
        <?php /* apple-touch-icon does not render SVG, so an SVG upload keeps
                 the bundled mark on a home screen. */ ?>
        <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->url->dir() ?>plugins/Shadcn/Assets/img/logo-180.png">
    <?php endif ?>
<?php else: ?>
    <link rel="icon" type="image/svg+xml" href="<?= $this->url->dir() ?>plugins/Shadcn/Assets/img/logo.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= $this->url->dir() ?>plugins/Shadcn/Assets/img/logo-32.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= $this->url->dir() ?>plugins/Shadcn/Assets/img/logo-512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= $this->url->dir() ?>plugins/Shadcn/Assets/img/logo-180.png">
<?php endif ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+TC:wght@400;500;700&display=swap">

<?php if ($shadcn_theme === 'dark'): ?>
    <?= $this->asset->css('plugins/Shadcn/Assets/css/theme-dark.css') ?>
<?php elseif ($shadcn_theme === 'auto'): ?>
    <?= $this->asset->css('plugins/Shadcn/Assets/css/theme-auto.css') ?>
<?php endif ?>

<?php /* Last, and deliberately so: theme-dark.css restates --primary for the
         dark palette, so the instance's own colour has to be declared after
         it to hold in both. Inline is permitted — the policy this plugin
         sets keeps 'unsafe-inline' for styles. */ ?>
<?php if ($shadcn_brand_css !== ''): ?>
<style><?= $shadcn_brand_css ?></style>
<?php endif ?>
