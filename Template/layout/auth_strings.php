<?php
/**
 * Copy for the auth screens, handed to the stylesheet and to auth.js.
 *
 * template:layout:head is the only hook Kanboard fires on a no_layout page,
 * which is what the login and password-reset screens are. An inline <style>
 * is permitted by the content security policy; an inline <script> is not, so
 * custom properties are the channel.
 */
$scTermsUrl = defined('TERMS_URL') ? TERMS_URL : '';
$scPrivacyUrl = defined('PRIVACY_POLICY_URL') ? PRIVACY_POLICY_URL : '';
?>
<style>:root {
    --sc-legal-terms: "<?= $this->text->e(t('Terms of Service')) ?>";
    --sc-legal-terms-url: "<?= $this->text->e($scTermsUrl) ?>";
    --sc-legal-privacy: "<?= $this->text->e(t('Privacy Policy')) ?>";
    --sc-legal-privacy-url: "<?= $this->text->e($scPrivacyUrl) ?>";
    --sc-new-label: "<?= $this->text->e(t('New')) ?>";
    --sc-brand-title: "<?= $this->text->e($this->shadcnSidebar->getBrandTitle()) ?>";
    --sc-brand-url: "<?= $this->text->e($this->shadcnSidebar->getBrandUrl()) ?>";
    --sc-pw-show: "<?= $this->text->e(t('Show password')) ?>";
    --sc-pw-hide: "<?= $this->text->e(t('Hide password')) ?>";
}</style>
