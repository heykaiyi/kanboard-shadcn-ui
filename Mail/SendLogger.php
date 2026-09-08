<?php

namespace Kanboard\Plugin\Shadcn\Mail;

use Swift_Events_SendEvent;
use Swift_Events_SendListener;

/**
 * Turns a silently dropped email into a log line.
 *
 * Kanboard's transport calls Swift_Mailer::send() and throws the return
 * value away. Swift only raises Swift_TransportException for connection
 * and handshake failures — a message the server refuses at RCPT (an
 * unroutable sender, a rejected recipient, a full mailbox) comes back as a
 * count of zero and nothing else. Nothing is logged, nothing is shown, and
 * the interface reports that notifications are working.
 */
class SendLogger implements Swift_Events_SendListener
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function beforeSendPerformed(Swift_Events_SendEvent $event)
    {
    }

    public function sendPerformed(Swift_Events_SendEvent $event)
    {
        if ($event->getResult() === Swift_Events_SendEvent::RESULT_SUCCESS) {
            return;
        }

        $message = $event->getMessage();
        $failed = $event->getFailedRecipients();

        $this->logger->error(sprintf(
            'Mail refused by the server: from=%s to=%s subject=%s failed=%s',
            implode(',', array_keys((array) $message->getFrom())),
            implode(',', array_keys((array) $message->getTo())),
            $message->getSubject(),
            empty($failed) ? '(none reported)' : implode(',', $failed)
        ));
    }
}
