<?php
/**
 * A progress bar at the foot of a board card that has sub-tasks.
 *
 * The footer already prints the ratio as "33% (1/3)"; this draws the same
 * figure. Rendered on template:board:task:footer, so no template is
 * overridden, and as a native <progress> so a screen reader announces it
 * and the theme's one Progress rule draws it.
 */
$scTotal = isset($task['nb_subtasks']) ? (int) $task['nb_subtasks'] : 0;

if ($scTotal === 0) {
    return;
}

$scDone = isset($task['nb_completed_subtasks']) ? (int) $task['nb_completed_subtasks'] : 0;
?>
<progress class="sc-task-progress" value="<?= $scDone ?>" max="<?= $scTotal ?>" aria-label="<?= $this->text->e(t('Sub-tasks')) ?> <?= $scDone ?>/<?= $scTotal ?>"></progress>
