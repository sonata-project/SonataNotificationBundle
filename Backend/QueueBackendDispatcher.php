<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Backend;

use Sonata\NotificationBundle\Model\MessageInterface;

/**
 * Base class for queue backent dispatchers.
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class QueueBackendDispatcher
 */
abstract class QueueBackendDispatcher implements QueueDispatcherInterface, BackendInterface
{
    /**
     * @var array
     */
    protected $queues;

    /**
     * @var string
     */
    protected $defaultQueue;

    /**
     * @var BackendInterface[]
     */
    protected $backends;

    /**
     * @param array              $queues
     * @param string             $defaultQueue
     * @param BackendInterface[] $backends
     */
    public function __construct(array $queues, $defaultQueue, array $backends)
    {
        $this->queues = $queues;
        $this->backends = $backends;
        $this->defaultQueue = $defaultQueue;

        foreach ($this->backends as $backend) {
            $backend['backend']->setDispatcher($this);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        $this->getBackend($message->getType())->publish($message);
    }

    /**
     * {@inheritdoc}
     */
    public function create($type, array $body)
    {
        return $this->getBackend($type)->create($type, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function createAndPublish($type, array $body)
    {
        $this->getBackend($type)->createAndPublish($type, $body);
    }

    /**
     * {@inheritdoc}
     */
    public function getQueues()
    {
        return $this->queues;
    }
}
