<?php

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
use Symfony\Component\HttpKernel\Log\LoggerInterface as LegacyLoggerInterface;

class LoggerConsumer implements ConsumerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string[]
     */
    protected $types = array(
        'emerg' => 'emergency',
        'alert' => 'alert',
        'crit' => 'critical',
        'err' => 'error',
        'warn' => 'warning',
        'notice' => 'notice',
        'info' => 'info',
        'debug' => 'debug',
    );

    /**
     * @param LoggerInterface|LegacyLoggerInterface $logger
     */
    public function __construct($logger)
    {
        if ($logger instanceof LegacyLoggerInterface) {
            trigger_error(sprintf('Using an instance of "%s" is deprecated since version 2.3. Use Psr\Log\LoggerInterface instead.', get_class($logger)), E_USER_DEPRECATED);
        }
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $message = $event->getMessage();

        if (!array_key_exists($message->getValue('level'), $this->types)) {
            throw new InvalidParameterException();
        }

        $level = $this->types[$message->getValue('level')];

        $this->logger->{$level}($message->getValue('message'));
    }
}
