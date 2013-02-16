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
    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * @param \Sonata\NotificationBundle\Model\MessageInterface $message
     */
    private function sendEmail(MessageInterface $message)
    {
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
