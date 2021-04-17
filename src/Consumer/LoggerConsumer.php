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

use Psr\Log\LoggerInterface;
use Sonata\NotificationBundle\Exception\InvalidParameterException;

class LoggerConsumer implements ConsumerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $types = [
        'emerg' => 'emergency',
        'alert' => 'alert',
        'crit' => 'critical',
        'err' => 'error',
        'warn' => 'warning',
        'notice' => 'notice',
        'info' => 'info',
        'debug' => 'debug',
    ];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function process(ConsumerEvent $event)
    {
        $message = $event->getMessage();

        if (!\array_key_exists($message->getValue('level'), $this->types)) {
            throw new InvalidParameterException();
        }

        $level = $this->types[$message->getValue('level')];

        $this->logger->{$level}($message->getValue('message'));
    }
}
