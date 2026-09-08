<?php
/**
 * The shadcn sidebar-07 shell.
 *
 * Rendered on template:layout:top, which puts these elements at the very top
 * of <body>, as siblings of Kanboard's own <header> and <section class="page">.
 * sidebar.css then makes <body> a grid and gives every one of them a named
 * area — including the three children of <header>, which are promoted into the
 * same grid with `display: contents`. Nothing is overridden and nothing is
 * duplicated: Kanboard's notification, creation and user menus keep their own
 * markup, their own JavaScript and their own extension hooks, and are simply
 * placed somewhere else on the page.
 *
 * Anonymous requests (login screen, public boards) get nothing at all.
 */

if (! $this->shadcnSidebar->isVisible()) {
    return;
}

$sidebarProject  = $this->shadcnSidebar->getProject();
$sidebarProjects = $this->shadcnSidebar->getProjects();

/**
 * The navigation group is the same on every page.
 *
 * A project's own views — overview, board, list, calendar, gantt, analytics —
 * are already a switcher inside the page, so repeating them in the sidebar
 * gave the same screen two different navigations that disagreed about where
 * you were.
 */
$sidebarItems = $this->shadcnSidebar->getDashboardItems();

?>
<aside class="sc-sb" id="sc-sidebar" aria-label="<?= t('Navigation') ?>">
    <div class="sc-sb-header">
        <a class="sc-sb-brand" href="<?= $this->url->href('DashboardController', 'show') ?>" aria-label="<?= $this->text->e($this->shadcnSidebar->getBrandTitle()) ?>">
            <span class="sc-sb-brand-mark" aria-hidden="true"></span>
            <span class="sc-sb-brand-text">
                <span class="sc-sb-brand-title"><?= $this->text->e($this->shadcnSidebar->getBrandTitle()) ?></span>
                <span class="sc-sb-brand-sub"><?= $this->text->e($this->shadcnSidebar->getBrandSubtitle()) ?></span>
            </span>
        </a>
        <button type="button" class="sc-sb-close" id="sc-sidebar-close" aria-controls="sc-sidebar">
            <i class="fa fa-times" aria-hidden="true"></i>
            <span class="sr-only"><?= t('Close') ?></span>
        </button>
    </div>

    <?php /* A phone has no ⌘K to press, so the drawer carries its own way
             into the palette. Hidden on a desktop, where the top bar has
             one already. */ ?>
    <button type="button" class="sc-command-trigger sc-command-trigger-sb">
        <span class="sc-command-trigger-search" aria-hidden="true"></span>
        <span class="sc-command-trigger-label"><?= t('Search') ?>…</span>
    </button>

    <nav class="sc-sb-content">
        <div class="sc-sb-group">
            <div class="sc-sb-group-label"><?= t('Navigation') ?></div>
            <ul class="sc-sb-menu">
                <?php foreach ($sidebarItems as $item): ?>
                    <li>
                        <a class="sc-sb-item<?= $item['active'] ? ' is-active' : '' ?><?= empty($item['modal']) ? '' : ' js-modal-medium' ?>"
                           href="<?= $this->url->href($item['controller'], $item['action'], $item['params']) ?>"
                           <?= $item['active'] ? 'aria-current="page"' : '' ?>
                           data-sc-tooltip="<?= $this->text->e($item['label']) ?>">
                            <i class="fa fa-fw fa-<?= $item['icon'] ?>" aria-hidden="true"></i>
                            <span class="sc-sb-item-label"><?= $this->text->e($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach ?>
            </ul>
        </div>

        <?php if (! empty($sidebarProjects)): ?>
            <div class="sc-sb-group sc-sb-group-projects">
                <div class="sc-sb-group-label"><?= t('Projects') ?></div>
                <ul class="sc-sb-menu">
                    <?php $sidebarShown = 0 ?>
                    <?php foreach ($sidebarProjects as $sidebarProjectId => $sidebarProjectName): ?>
                        <?php if ($sidebarShown++ >= \Kanboard\Plugin\Shadcn\Helper\SidebarHelper::PROJECT_LIMIT) { break; } ?>
                        <?php $sidebarIsCurrent = ! empty($sidebarProject) && (int) $sidebarProject['id'] === (int) $sidebarProjectId ?>
                        <li>
                            <a class="sc-sb-item<?= $sidebarIsCurrent ? ' is-active' : '' ?>"
                               href="<?= $this->url->href('BoardViewController', 'show', array('project_id' => $sidebarProjectId)) ?>"
                               data-sc-tooltip="<?= $this->text->e($sidebarProjectName) ?>">
                                <i class="fa fa-fw fa-folder" aria-hidden="true"></i>
                                <span class="sc-sb-item-label"><?= $this->text->e($sidebarProjectName) ?></span>
                            </a>
                        </li>
                    <?php endforeach ?>
                    <li>
                        <a class="sc-sb-item sc-sb-item-muted"
                           href="<?= $this->url->href('ProjectListController', 'show') ?>"
                           data-sc-tooltip="<?= t('Projects management') ?>">
                            <i class="fa fa-fw fa-ellipsis-h" aria-hidden="true"></i>
                            <span class="sc-sb-item-label"><?= t('More') ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        <?php endif ?>
    </nav>

    <div class="sc-sb-secondary">
        <?php /* Kanboard's documentation is a website, so it opens in a tab
                 of its own rather than replacing the board you were on. */ ?>
        <a class="sc-sb-item"
           href="<?= $this->shadcnSidebar->getDocumentationUrl() ?>"
           target="_blank" rel="noopener noreferrer"
           data-sc-tooltip="<?= t('Documentation') ?>">
            <i class="fa fa-fw fa-life-ring" aria-hidden="true"></i>
            <span class="sc-sb-item-label"><?= t('Documentation') ?></span>
        </a>
    </div>
</aside>

<a class="sc-topbar-brand" href="<?= $this->url->href('DashboardController', 'show') ?>" aria-label="<?= $this->text->e($this->shadcnSidebar->getBrandTitle()) ?>">
    <span class="sc-topbar-brand-mark" aria-hidden="true"></span>
    <span class="sc-topbar-brand-name">
        <span class="sc-topbar-brand-full"><?= $this->text->e($this->shadcnSidebar->getBrandTitle()) ?></span>
        <span class="sc-topbar-brand-short"><?= $this->text->e($this->shadcnSidebar->getBrandShortTitle()) ?></span>
    </span>
</a>

<div class="sc-command-slot">
    <button type="button" class="sc-command-trigger" id="sc-command-trigger">
        <span class="sc-command-trigger-search" aria-hidden="true"></span>
        <span class="sc-command-trigger-label"><?= t('Search') ?>…</span>
        <kbd>⌘K</kbd>
    </button>
</div>

<nav class="sc-crumbs" aria-label="<?= t('Breadcrumb') ?>">
    <ol>
        <?php $sidebarCrumbs = $this->shadcnSidebar->getBreadcrumb() ?>
        <?php foreach ($sidebarCrumbs as $sidebarIndex => $sidebarCrumb): ?>
            <li<?= empty($sidebarCrumb['home']) ? '' : ' class="sc-crumb-home"' ?>>
                <?php if (! empty($sidebarCrumb['home'])): ?>
                    <a href="<?= $sidebarCrumb['url'] ?>" title="<?= $this->text->e($sidebarCrumb['label']) ?>">
                        <i class="fa fa-home" aria-hidden="true"></i>
                        <span class="sc-crumb-home-text"><?= $this->text->e($sidebarCrumb['label']) ?></span>
                    </a>
                <?php elseif ($sidebarCrumb['url'] === null): ?>
                    <span aria-current="page"><?= $this->text->e($sidebarCrumb['label']) ?></span>
                <?php else: ?>
                    <a href="<?= $sidebarCrumb['url'] ?>"><?= $this->text->e($sidebarCrumb['label']) ?></a>
                <?php endif ?>
            </li>
        <?php endforeach ?>
    </ol>
</nav>

<div class="sc-sb-user-meta" aria-hidden="true">
    <span class="sc-sb-user-avatar"><?= $this->shadcnSidebar->renderUserAvatar(48) ?></span>
    <span class="sc-sb-user-text">
        <span class="sc-sb-user-name"><?= $this->text->e($this->user->getFullname()) ?><?php if ($this->shadcnSidebar->isAdmin()): ?><i class="sc-verified" aria-hidden="true" title="<?= t('Administrator') ?>"></i><?php endif ?></span>
    <?php $sidebarEmail = $this->shadcnSidebar->getUserEmail() ?>
    <?php if ($sidebarEmail !== ''): ?>
        <span class="sc-sb-user-mail"><?= $this->text->e($sidebarEmail) ?></span>
        <?php endif ?>
    </span>
</div>

<button type="button" class="sc-sb-rail" id="sc-sidebar-rail"
        aria-controls="sc-sidebar" tabindex="-1"
        title="<?= t('Toggle navigation') ?>"><span class="sr-only"><?= t('Toggle navigation') ?></span></button>

<button type="button" class="sc-sb-trigger" id="sc-sidebar-trigger" aria-controls="sc-sidebar">
    <i class="fa fa-bars" aria-hidden="true"></i>
    <span class="sr-only"><?= t('Toggle navigation') ?></span>
</button>

<div class="sc-sb-overlay" id="sc-sidebar-overlay"></div>
