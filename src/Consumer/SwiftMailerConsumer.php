<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Consumer;

use Sonata\NotificationBundle\Model\MessageInterface;

/**
 * @final since sonata-project/notification-bundle 3.13
 */
class SwiftMailerConsumer implements ConsumerInterface
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function process(ConsumerEvent $event)
    {
        if (!$this->mailer->getTransport()->isStarted()) {
            $this->mailer->getTransport()->start();
        }

        $exception = false;

        try {
            $this->sendEmail($event->getMessage());
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->mailer->getTransport()->stop();

        if ($exception) {
            throw $exception;
        }
    }

    private function sendEmail(MessageInterface $message)
    {
        $mail = $this->mailer->createMessage()
            ->setSubject($message->getValue('subject'))
            ->setFrom([$message->getValue(['from', 'email']) => $message->getValue(['from', 'name'])])
            ->setTo($message->getValue('to'));

        if ($replyTo = $message->getValue('replyTo')) {
            $mail->setReplyTo($replyTo);
        }
        if ($returnPath = $message->getValue('returnPath')) {
            $mail->setReturnPath($returnPath);
        }

        if ($cc = $message->getValue('cc')) {
            $mail->setCc($cc);
        }

        if ($bcc = $message->getValue('bcc')) {
            $mail->setBcc($bcc);
        }

        if ($text = $message->getValue(['message', 'text'])) {
            $mail->addPart($text, 'text/plain');
        }

        if ($html = $message->getValue(['message', 'html'])) {
            $mail->addPart($html, 'text/html');
        }

        if ($attachment = $message->getValue(['attachment', 'file'])) {
            $attachmentName = $message->getValue(['attachment', 'name']);

            $mail->attach(new \Swift_Attachment($attachment, $attachmentName));
        }

        $this->mailer->send($mail);
    }
}
