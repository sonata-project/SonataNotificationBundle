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

use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Sonata\NotificationBundle\Exception\InvalidParameterException;

class LoggerConsumer implements ConsumerInterface
{
    protected $logger;

    protected $types = array(
        'emerg',
        'alert',
        'crit',
        'err',
        'warn',
        'notice',
        'info',
        'debug',
    );

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $message = $event->getMessage();

        if (!in_array($message->getValue('level'), $this->types)) {
            throw new InvalidParameterException();
        }

        call_user_func(array($this->logger, $message->getValue('level')), $message->getValue('message'));
    }
}
