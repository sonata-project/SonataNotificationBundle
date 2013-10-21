<?php

/*
 * This file is part of the Sonata project.
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
use Symfony\Component\EventDispatcher\Event;

/**
 * Event for ConsumerHandlerCommand iterations event
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class IterateEvent
 * @package Sonata\NotificationBundle\Event
 */
class IterateEvent extends Event
{
    const EVENT_NAME = 'sonata.notification.event.message_iterate_event';

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
     * @param MessageIteratorInterface $iterator
     * @param BackendInterface         $backend
     * @param MessageInterface         $message
     */
    public function __construct(MessageIteratorInterface $iterator, BackendInterface $backend = null, MessageInterface $message = null)
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