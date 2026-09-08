<?php
/**
 * The brand mark and heading of shadcn's login-02 block.
 *
 * Rendered on template:auth:login-form:before, which only exists in
 * app/Template/auth/index.php — the password-reset screens carry their own
 * <h2> and are styled to match. Kept as a template rather than CSS `content`
 * so the copy goes through t() like every other string.
 */
?>
<?php /* login-02 labels its social row; CSS content() cannot be translated,
       * so the string is handed to the stylesheet as a custom property.
       * style-src allows 'unsafe-inline', script-src does not. */ ?>
<style>:root { --sc-or-label: "<?= $this->text->e(t('Or continue with')) ?>"; }</style>

<a class="sc-auth-brand" href="<?= $this->shadcnSidebar->getBrandUrl() ?>" aria-label="<?= $this->text->e($this->shadcnSidebar->getBrandTitle()) ?>">
    <span class="sc-auth-brand-mark" aria-hidden="true"></span>
    <span class="sc-auth-brand-title"><?= $this->text->e($this->shadcnSidebar->getBrandTitle()) ?></span>
</a>

<div class="sc-auth-head">
    <h1><?= t('Login to your account') ?></h1>
    <p><?= t('Enter your username below to sign in') ?></p>
</div>
