<?php

namespace Kanboard\Plugin\Shadcn\Mail;

use Kanboard\Core\Mail\Client;

/**
 * The mail client, with one shell around every message.
 *
 * Kanboard has no mail layout: each of the twenty-odd templates under
 * app/Template/notification/ emits its own <html><body> and is styled by
 * nothing at all. Overriding them one by one would mean carrying twenty
 * copies of upstream markup and re-merging them on every release.
 *
 * Wrapping instead happens at the single point every message passes
 * through — the mail client — so plugin-generated mail (Wiki, Turnstile,
 * anything installed later) is wrapped too, and no upstream template is
 * duplicated.
 */
class LayoutClient extends Client
{
    public function send($recipientEmail, $recipientName, $subject, $html, $authorName = null, $authorEmail = null)
    {
        return parent::send(
            $recipientEmail,
            $recipientName,
            $subject,
            $this->wrap($subject, $html),
            $authorName,
            $authorEmail
        );
    }

    /**
     * Lift the message out of whatever document it arrived in and re-serve
     * it inside ours.
     *
     * Templates that already carry <html><body> hand over their body; a
     * fragment is taken as-is. Either way the result is wrapped exactly
     * once, so a second pass over an already-wrapped message is not
     * possible.
     */
    private function wrap($subject, $html)
    {
        $content = $html;

        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $matches) === 1) {
            $content = $matches[1];
        } else {
            $content = preg_replace('/<\/?(?:html|head|body)[^>]*>/i', '', $html);
        }

        return $this->template->render('shadcn:mail/layout', array(
            'content' => trim($content),
            'subject' => $subject,
        ));
    }
}
