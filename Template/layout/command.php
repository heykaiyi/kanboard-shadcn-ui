<?php
/**
 * The command palette.
 *
 * Kanboard's project switcher is an autocomplete input pinned to the top
 * bar; this is the overlay a ⌘K is expected to open — one field, a grouped
 * list of everywhere you can go, and the keyboard to move through it.
 *
 * The list is rendered server-side because only the server knows which
 * projects this user may open. Assets/js/command.js does the filtering.
 */
if (! $this->shadcnSidebar->isVisible()) {
    return;
}

$scCommands = $this->shadcnSidebar->getCommandItems();
$scGroups = array();

foreach ($scCommands as $scCommand) {
    $scGroups[$scCommand['group']][] = $scCommand;
}
?>
<div class="sc-command" id="sc-command" hidden>
    <div class="sc-command-overlay" data-sc-command-close></div>

    <div class="sc-command-box" role="dialog" aria-modal="true" aria-label="<?= t('Search') ?>">
        <div class="sc-command-field">
            <span class="sc-command-search" aria-hidden="true"></span>
            <input type="text" id="sc-command-input" autocomplete="off" spellcheck="false"
                   placeholder="<?= t('Search') ?>…" aria-label="<?= t('Search') ?>">
            <kbd class="sc-command-esc">ESC</kbd>
        </div>

        <div class="sc-command-tabs" role="tablist">
            <button type="button" class="sc-command-tab is-active" data-group="" role="tab"><?= t('All') ?></button>
            <?php foreach (array_keys($scGroups) as $scGroupName): ?>
                <button type="button" class="sc-command-tab" data-group="<?= $this->text->e($scGroupName) ?>" role="tab">
                    <?= $this->text->e($scGroupName) ?>
                </button>
            <?php endforeach ?>
        </div>

        <div class="sc-command-list" id="sc-command-list">
            <?php foreach ($scGroups as $scGroupName => $scGroupItems): ?>
                <div class="sc-command-group" data-group="<?= $this->text->e($scGroupName) ?>">
                    <div class="sc-command-group-label"><?= $this->text->e($scGroupName) ?></div>
                    <?php foreach ($scGroupItems as $scItem): ?>
                        <a class="sc-command-item<?= empty($scItem['current']) ? '' : ' is-current' ?>"
                           href="<?= $scItem['url'] ?>"
                           <?= empty($scItem['external']) ? '' : 'target="_blank" rel="noopener noreferrer"' ?>
                           data-label="<?= $this->text->e(mb_strtolower($scItem['label'])) ?>">
                            <i class="fa fa-fw fa-<?= $scItem['icon'] ?>" aria-hidden="true"></i>
                            <span><?= $this->text->e($scItem['label']) ?></span>
                            <?php if (! empty($scItem['current'])): ?>
                                <span class="sc-command-item-note"><?= t('Current') ?></span>
                            <?php endif ?>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
            <p class="sc-command-empty" hidden><?= t('No results') ?></p>
        </div>
    </div>
</div>
