<?php

namespace Kanboard\Plugin\Shadcn\Mail;

use Kanboard\Core\Mail\Transport\Smtp;

/**
 * Kanboard's SMTP transport, with refusals written to the log.
 *
 * Only getTransport() is overridden, so the message itself is still built
 * entirely by upstream — no copy of Swift_Message construction to keep in
 * step across releases.
 */
class LoggingSmtpTransport extends Smtp
{
    protected function getTransport()
    {
        $transport = parent::getTransport();
        $transport->registerPlugin(new SendLogger($this->logger));

        return $transport;
    }
}
