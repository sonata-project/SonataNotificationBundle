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

namespace Sonata\NotificationBundle\Event;

use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Iterator\MessageIteratorInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event for ConsumerHandlerCommand iterations event.
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * @final since sonata-project/notification-bundle 3.13
 */
class IterateEvent extends Event
{
    public const EVENT_NAME = 'sonata.notification.event.message_iterate_event';

    /**
     * @var MessageIteratorInterface
     */
    protected $iterator;

    /**
     * @var BackendInterface
     */
    protected $backend;

    /**
     * @var MessageInterface
     */
    protected $message;

    /**
     * @param BackendInterface $backend
     * @param MessageInterface $message
     */
    public function __construct(MessageIteratorInterface $iterator, ?BackendInterface $backend = null, ?MessageInterface $message = null)
    {
        $this->iterator = $iterator;
        $this->backend = $backend;
        $this->message = $message;
    }

    /**
     * @return BackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * @return MessageIteratorInterface
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }
}
