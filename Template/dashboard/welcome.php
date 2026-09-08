<?php
/**
 * The dashboard's welcome row.
 *
 * Rendered on template:dashboard:show:before-filter-box, which sits at the
 * very top of the overview. Every number comes from Kanboard's own
 * user-scoped queries — none of them are decorative.
 */
$scStats = $this->shadcnSidebar->getDashboardStats();
?>
<div class="sc-welcome">
    <h1 class="sc-welcome-title">
        <?= t('Welcome back, %s', $this->text->e($this->shadcnSidebar->getGreetingName())) ?> 👋
    </h1>
    <p class="sc-welcome-sub"><?= t('Your projects, what is open, and what is due next.') ?></p>
</div>

<div class="sc-stats">
    <?php foreach ($scStats as $scStat): ?>
        <a class="sc-stat sc-stat-<?= $scStat['tone'] ?>" href="<?= $scStat['url'] ?>">
            <span class="sc-stat-head">
                <span class="sc-stat-label"><?= $this->text->e($scStat['label']) ?></span>
                <i class="fa fa-<?= $scStat['icon'] ?>" aria-hidden="true"></i>
            </span>
            <span class="sc-stat-value"><?= (int) $scStat['value'] ?></span>
        </a>
    <?php endforeach ?>
</div>
