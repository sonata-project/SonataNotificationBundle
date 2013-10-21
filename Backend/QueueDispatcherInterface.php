<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Backend;

/**
 * A QueueDispatcherInterface acts as a router for different
 * queue types.
 *
 * @see AMQPBackendDispatcher for an eample implementation.
 *
 */
interface QueueDispatcherInterface
{
    /**
     * Get a backend by message type.
     *
     * @param string $type
     *
     * @throws \Sonata\NotificationBundle\Exception\BackendNotFoundException
     *
     * @return BackendInterface
     */
    public function getBackend($type);

    /**
     * Get all registered queues.
     *
     * @return array
     */
    public function getQueues();
}
