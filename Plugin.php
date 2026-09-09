<?php

namespace Kanboard\Plugin\Shadcn;

use Kanboard\Core\Plugin\Base;
use Kanboard\Core\Security\Role;
use Kanboard\Core\Translator;
use Kanboard\Plugin\Shadcn\Model\BrandingModel;

/**
 * Shadcn theme for Kanboard
 *
 * Restyles the whole application with the shadcn/ui design language and
 * replaces the bundled Font Awesome glyphs with HugeIcons, entirely through
 * stylesheets appended after Kanboard's own. No core file is modified and no
 * template is overridden, so the theme survives a Kanboard upgrade.
 *
 * Load order matters and is guaranteed by app/Template/layout.php:
 *
 *   1. vendor.min.css                       (Font Awesome, select2, jQuery UI)
 *   2. light|dark|auto.min.css              (Kanboard's own variables)
 *   3. customCss()                          (instance stylesheet from Settings)
 *   4. template:layout:css hook             <- tokens, components, icons, sidebar
 *   5. template:layout:head hook            <- per-user dark token overrides
 *
 * Steps 4 and 5 are ours, so equal-specificity rules win without !important.
 *
 * The sidebar is the one part of the theme that ships its own markup. It is
 * rendered on template:layout:top, which Kanboard emits at the very top of
 * <body>, so the plugin still overrides no template and modifies no core file.
 */
class Plugin extends Base
{
    public function initialize()
    {
        // 0. The sidebar needs the current project, the user's project list
        //    and the active route. template:layout:top is rendered without
        //    parameters, so a helper resolves all of it.
        $this->helper->register('shadcnSidebar', '\Kanboard\Plugin\Shadcn\Helper\SidebarHelper');

        // 0b. The branding — mark, tab icon, name and accent colour. The
        //     model owns the values, the helper turns them into the URLs and
        //     the custom properties the templates ask for.
        $this->container['shadcnBrandingModel'] = new BrandingModel($this->container);
        $this->helper->register('shadcnBrand', '\Kanboard\Plugin\Shadcn\Helper\BrandHelper');

        // 0c. The uploaded mark and tab icon live in the data directory,
        //     which is not reachable over HTTP, so they are served by a
        //     route. It has to be public: the login screen has a mark on it
        //     and nobody is signed in there yet. The settings screen itself
        //     is a ConfigController, so it inherits the admin-only rule
        //     Kanboard already has for that name.
        $this->applicationAccessMap->add('BrandingController', 'image', Role::APP_PUBLIC);

        // 0d. Settings → Appearance, next to every other configuration page,
        //     on a path that reads like the ones beside it.
        $this->template->hook->attach('template:config:sidebar', 'shadcn:config/sidebar');
        $this->route->addRoute('settings/brand', 'ConfigController', 'show', 'Shadcn');
        $this->route->addRoute('settings/brand/save', 'ConfigController', 'save', 'Shadcn');
        // The CSRF token is appended as a query string by UrlHelper after the
        // route is resolved, so it cannot be a path segment here.
        $this->route->addRoute('settings/brand/remove/:image', 'ConfigController', 'remove', 'Shadcn');

        // 1. shadcn tokens, plus the bridge that redefines Kanboard's own
        //    custom properties in terms of them.
        $this->hook->on('template:layout:css', array(
            'template' => 'plugins/Shadcn/Assets/css/tokens.css',
        ));

        // 2. Component styling for the surfaces Kanboard actually renders.
        $this->hook->on('template:layout:css', array(
            'template' => 'plugins/Shadcn/Assets/css/components.css',
        ));

        // 3. HugeIcons in place of the Font Awesome glyphs.
        $this->hook->on('template:layout:css', array(
            'template' => 'plugins/Shadcn/Assets/css/icons.css',
        ));

        // 4. The sidebar-07 shell. Loaded after the component work so it can
        //    re-place Kanboard's header without fighting it.
        $this->hook->on('template:layout:css', array(
            'template' => 'plugins/Shadcn/Assets/css/sidebar.css',
        ));

        // 5. Collapse-to-icon, the narrow-screen drawer, and Cmd/Ctrl-B.
        $this->hook->on('template:layout:js', array(
            'template' => 'plugins/Shadcn/Assets/js/sidebar.js',
        ));

        // 5b. The notification panel opens as a Sheet rather than a Dialog.
        $this->hook->on('template:layout:js', array(
            'template' => 'plugins/Shadcn/Assets/js/modal.js',
        ));

        // 5d. A progress line while the next page loads, and one title
        //     format across the app.
        $this->hook->on('template:layout:js', array(
            'template' => 'plugins/Shadcn/Assets/js/navigation.js',
        ));

        // 5c. The auth screens' legal footer — real links, on screens that
        //     have no hook to render into.
        $this->hook->on('template:layout:js', array(
            'template' => 'plugins/Shadcn/Assets/js/auth.js',
        ));

        // 5e. Live swatch and image previews on Settings → Appearance. It
        //     returns at once on every other screen.
        $this->hook->on('template:layout:js', array(
            'template' => 'plugins/Shadcn/Assets/js/branding.js',
        ));

        // 5f. A way to see what was typed into a password field. Every one
        //     of them, including the ones a sheet loads later.
        $this->hook->on('template:layout:js', array(
            'template' => 'plugins/Shadcn/Assets/js/password.js',
        ));

        $this->template->hook->attach('template:layout:head', 'shadcn:layout/auth_strings');

        // 6. Dark tokens, chosen from the user's own Kanboard theme setting.
        //    Kept in a template because the choice is per-request.
        $this->template->hook->attach('template:layout:head', 'shadcn:layout/head');

        // 6a. The type stack needs Noto Sans TC to actually be fetchable.
        //     Read-then-write so a sibling plugin's additions survive.
        $rules = $this->container['cspRules'];
        $rules['style-src'] = "'self' 'unsafe-inline' https://fonts.googleapis.com";
        $rules['font-src'] = "'self' https://fonts.gstatic.com data:";
        $this->setContentSecurityPolicy($rules);

        // 6b. The dashboard opens with a greeting and four real numbers.
        $this->template->hook->attach('template:dashboard:show:before-filter-box', 'shadcn:dashboard/welcome');

        // 7. The login screen's heading, so login-02 has something to head it
        //    with that still goes through t().
        $this->template->hook->attach('template:auth:login-form:before', 'shadcn:auth/header');

        // 7b. The command palette, and the script that drives it.
        $this->hook->on('template:layout:js', array(
            'template' => 'plugins/Shadcn/Assets/js/command.js',
        ));
        $this->template->hook->attach('template:layout:top', 'shadcn:layout/command');

        // 8. The sidebar markup itself, at the top of <body>.
        $this->template->hook->attach('template:layout:top', 'shadcn:layout/sidebar');

        // 9. One shell around every outgoing email.
        //
        //    Kanboard has no mail layout at all — each notification template
        //    emits its own bare <html><body>. Rather than override twenty of
        //    them and re-merge upstream on every release, the mail client is
        //    swapped for one that wraps whatever it is handed. Plugin mail
        //    goes through the same client, so it is wrapped too.
        //
        //    Pimple freezes a key once it has been read; nothing has asked
        //    for emailClient this early, so the reassignment is safe.
        $this->container['emailClient'] = function ($container) {
            $mailer = new \Kanboard\Plugin\Shadcn\Mail\LayoutClient($container);
            // Upstream's SMTP transport, plus a listener that logs the messages
            // the server refuses. Swift returns a count of zero for those rather
            // than throwing, and Kanboard discards the count — so without this a
            // rejected sender address looks exactly like a working mail setup.
            $mailer->setTransport('smtp', '\Kanboard\Plugin\Shadcn\Mail\LoggingSmtpTransport');
            $mailer->setTransport('sendmail', '\Kanboard\Core\Mail\Transport\Sendmail');
            $mailer->setTransport('mail', '\Kanboard\Core\Mail\Transport\Mail');

            return $mailer;
        };

        // 9b. Every notification template ends by rendering this partial, so
        //     one override gives all of them the same call to action.
        $this->template->setTemplateOverride('notification/footer', 'shadcn:notification/footer');
    }

    public function onStartup()
    {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__.'/Locale');
    }

    public function getPluginName()
    {
        return 'Shadcn';
    }

    public function getPluginDescription()
    {
        return t('shadcn/ui design language and HugeIcons for the whole Kanboard interface');
    }

    public function getPluginAuthor()
    {
        return 'kaiyi';
    }

    public function getPluginVersion()
    {
        return '0.34.0';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/kanboard/kanboard';
    }

    public function getCompatibleVersion()
    {
        return '>=1.2.0';
    }
}
