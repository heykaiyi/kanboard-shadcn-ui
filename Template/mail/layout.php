<?php
/**
 * The one shell every notification is served in.
 *
 * Built the way an email has to be built rather than the way a page is:
 * nested tables, every structural rule inline, one 600px card centred on a
 * tinted ground. Outlook renders with the Word engine and Gmail strips what
 * it does not recognise, so this is deliberately the markup of 2005 — it is
 * the only markup that arrives intact.
 *
 * The <style> block is for the message body, which comes from Kanboard's
 * own templates and cannot be given inline rules without copying all
 * twenty of them. Gmail, Apple Mail and Thunderbird honour it; Outlook
 * ignores most of it, which is why every fallback below is readable on its
 * own — plain text on white at a sensible size.
 */
$appUrl = $this->app->config('application_url');
$host = $appUrl === '' ? '' : rtrim(preg_replace('#^https?://#', '', $appUrl), '/');

/* Mail clients need an absolute URL — there is no page to be relative to.
 * A 180px source drawn at 36px so it stays sharp on a retina screen, and
 * never an SVG: no client draws one. */
$logo = $this->shadcnBrand->getMailLogoUrl();
$brand = $this->shadcnSidebar->getBrandTitle();

/* An email has no custom properties, so the instance's accent has to be
 * written into the markup. The stripe carries it whatever it is; body links
 * only take it when it is dark enough to read on white. */
$accent = $this->shadcnBrand->getColor();
$accentLink = $this->shadcnBrand->getLinkColor();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="color-scheme" content="light" />
<meta name="supported-color-schemes" content="light" />
<title><?= $this->text->e($subject) ?></title>
<style type="text/css">
    body { margin: 0; padding: 0; background-color: #f6f7f9; }
    table { border-collapse: collapse; }
    img { border: 0; outline: none; text-decoration: none; -ms-interpolation-mode: bicubic; }

    .sc-content { color: #0a0a0a; font-size: 15px; line-height: 1.7; }
    .sc-content h1, .sc-content h2, .sc-content h3 {
        margin: 0 0 14px; color: #0a0a0a; font-weight: 700; line-height: 1.4;
    }
    .sc-content h1 { font-size: 19px; }
    .sc-content h2 { font-size: 17px; }
    .sc-content h3 { font-size: 15px; }
    .sc-content h2 + ul, .sc-content h2 + p { margin-top: 0; }
    .sc-content p { margin: 0 0 14px; }
    .sc-content ul, .sc-content ol { margin: 0 0 18px; padding-left: 20px; }
    .sc-content li { margin: 0 0 7px; }
    .sc-content strong { font-weight: 600; }
    .sc-content a { color: <?= $accentLink ?>; text-decoration: underline; }
    .sc-content hr { height: 1px; margin: 26px 0; border: 0; background-color: #e5e5e5; }
    .sc-content blockquote {
        margin: 0 0 16px; padding: 8px 0 8px 16px;
        border-left: 3px solid #e5e5e5; color: #737373;
    }
    .sc-content code {
        padding: 2px 5px; border-radius: 4px; background-color: #f5f5f5;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px;
    }
    .sc-content pre {
        margin: 0 0 16px; padding: 12px 14px; border-radius: 8px; overflow-x: auto;
        background-color: #f5f5f5;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px;
    }
    .sc-content img { max-width: 100%; height: auto; }
    .sc-content table { width: 100%; margin: 0 0 16px; font-size: 14px; }
    .sc-content th, .sc-content td { padding: 8px 10px; border-bottom: 1px solid #e5e5e5; text-align: left; }

    @media only screen and (max-width: 620px) {
        .sc-card { width: 100% !important; }
        .sc-pad { padding-left: 24px !important; padding-right: 24px !important; }
    }
</style>
</head>
<body style="margin:0; padding:0; background-color:#f6f7f9;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f6f7f9;">
<tr>
<td align="center" style="padding:0;">

    <table role="presentation" class="sc-card" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px; max-width:600px; background-color:#ffffff;">

        <?php /* The brand's one stripe, the full width of the card. */ ?>
        <tr><td style="height:6px; background-color:<?= $accent ?>; font-size:0; line-height:0;">&nbsp;</td></tr>

        <tr>
        <td class="sc-pad" style="padding:32px 40px 0 40px;">
            <?php /* Most clients block remote images until asked, so the
                     wordmark sits beside the logo rather than inside it —
                     the header still reads as a brand with images off. */ ?>
            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <?php if ($logo !== ''): ?>
                <td width="36" style="width:36px; padding-right:10px;">
                    <a href="<?= $this->text->e($appUrl) ?>" style="display:block; text-decoration:none;"><img src="<?= $this->text->e($logo) ?>" width="36" height="36" alt="<?= $this->text->e($brand) ?>" style="display:block; width:36px; height:36px; border:0;" /></a>
                </td>
                <?php endif ?>
                <td style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Noto Sans TC','PingFang TC','Microsoft JhengHei',Roboto,Helvetica,Arial,sans-serif; font-size:20px; font-weight:700; line-height:1.3;">
                    <?php if ($appUrl !== ''): ?>
                        <a href="<?= $this->text->e($appUrl) ?>" style="color:#0a0a0a; text-decoration:none;"><?= $this->text->e($brand) ?></a>
                    <?php else: ?>
                        <span style="color:#0a0a0a;"><?= $this->text->e($brand) ?></span>
                    <?php endif ?>
                </td>
            </tr>
            </table>
        </td>
        </tr>

        <tr>
        <td class="sc-pad sc-content" style="padding:22px 40px 8px 40px; color:#0a0a0a; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Noto Sans TC','PingFang TC','Microsoft JhengHei',Roboto,Helvetica,Arial,sans-serif; font-size:15px; line-height:1.7;">
<?= $content ?>
        </td>
        </tr>

        <?php /* Why this arrived, and whose instance sent it. */ ?>
        <tr>
        <?php /* Generous space above the rule: the outline button below the
                 content has a border of its own, and a divider too close to
                 it reads as one doubled line. */ ?>
        <td class="sc-pad" style="padding:30px 40px 28px 40px;">
            <div style="height:1px; margin-bottom:20px; background-color:#e5e5e5; font-size:0; line-height:0;">&nbsp;</div>
            <p style="margin:0 0 10px; color:#737373; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Noto Sans TC',Roboto,Helvetica,Arial,sans-serif; font-size:12px; line-height:1.7;">
                <?= t('You are receiving this email because you are subscribed to notifications on %s.', $brand) ?>
                <?= t('This message may contain confidential information. If it was not meant for you, please delete it and do not forward it.') ?>
            </p>
            <p style="margin:0; color:#a3a3a3; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Noto Sans TC',Roboto,Helvetica,Arial,sans-serif; font-size:12px; line-height:1.7;">
                &copy; <?= date('Y') ?> <?= $this->text->e($brand) ?><?php if ($host !== ''): ?> &middot; <a href="<?= $this->text->e($appUrl) ?>" style="color:#a3a3a3; text-decoration:underline;"><?= $this->text->e($host) ?></a><?php endif ?>
            </p>
        </td>
        </tr>

    </table>

</td>
</tr>
</table>
</body>
</html>
