<?php
/**
 * Replaces app/Template/notification/footer.php, which is an <hr/>, the
 * word "Kanboard" and two bare links.
 *
 * Every core notification template ends by rendering this partial, so one
 * override gives all of them the same pair of actions. Full-width buttons:
 * on a phone a 120px button in the middle of a column of text is a target
 * you have to aim for.
 *
 * Inline styles only — this fragment is dropped into the shell's content
 * cell, and Outlook would ignore a class here.
 */
$scUrl = $this->app->config('application_url');
$scFont = "-apple-system,BlinkMacSystemFont,'Segoe UI','Noto Sans TC','PingFang TC','Microsoft JhengHei',Roboto,Helvetica,Arial,sans-serif";

/* The primary button is the one place in an email that fills with the
 * instance's accent, so its label takes the same computed foreground the
 * interface uses — black on a pale brand, white on a dark one. */
$scAccent = $this->shadcnBrand->getColor();
$scAccentFg = $this->shadcnBrand->getForegroundColor();
?>
<?php if (! empty($scUrl)): ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:10px;">
    <?php if (isset($task['id'])): ?>
    <tr>
    <td align="center" style="padding:0 0 10px 0; background-color:#ffffff;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:<?= $scAccent ?>;">
        <tr><td align="center" style="padding:0;">
            <a href="<?= $this->url->href('TaskViewController', 'show', array('task_id' => $task['id']), false, '', true) ?>"
               style="display:block; padding:14px 20px; color:<?= $scAccentFg ?>; font-family:<?= $scFont ?>; font-size:15px; font-weight:700; text-align:center; text-decoration:none;"><?= t('Open the task') ?></a>
        </td></tr>
        </table>
    </td>
    </tr>
    <?php endif ?>
    <?php if (isset($task['project_id'])): ?>
    <tr>
    <td align="center" style="padding:0;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e5e5;">
        <tr><td align="center" style="padding:0;">
            <a href="<?= $this->url->href('BoardViewController', 'show', array('project_id' => $task['project_id']), false, '', true) ?>"
               style="display:block; padding:13px 20px; color:#0a0a0a; font-family:<?= $scFont ?>; font-size:15px; font-weight:700; text-align:center; text-decoration:none;"><?= t('Open the board') ?></a>
        </td></tr>
        </table>
    </td>
    </tr>
    <?php endif ?>
</table>
<?php endif ?>
