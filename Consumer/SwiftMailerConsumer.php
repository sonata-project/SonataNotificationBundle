<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Consumer;

use Sonata\NotificationBundle\Model\MessageInterface;

class SwiftMailerConsumer implements ConsumerInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @param $vendorDir
     */
    public function __construct($vendorDir)
    {
        require_once sprintf('%s/lib/classes/Swift.php', $vendorDir);

        if (!\Swift::$initialized) {
            \Swift::registerAutoload(sprintf('%s/lib/swift_init.php', $vendorDir));
        }
    }

    /**
     * @param \Swift_Mailer $mailer
     */
    public function setMailer(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $message = $event->getMessage();

        $mail = $this->mailer->createMessage()
            ->setSubject($message->getValue('subject'))
            ->setFrom(array($message->getValue(array('from', 'email')) => $message->getValue(array('from', 'name'))))
            ->setTo($message->getValue('to'));

        if ($text = $message->getValue(array('message', 'text'))) {
            $mail->addPart($text, 'text/plain');
        }

        if ($html = $message->getValue(array('message', 'html'))) {
            $mail->addPart($html, 'text/html');
        }

        $this->mailer->send($mail);
    }
}