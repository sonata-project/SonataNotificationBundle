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

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sonata\NotificationBundle\Model\MessageInterface;

use Liip\Monitor\Result\CheckResult;

/**
 * Base class for queue backent dispatchers
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class QueueBackendDispatcher
 * @package Sonata\NotificationBundle\Backend
 */
abstract class QueueBackendDispatcher implements QueueDispatcherInterface, BackendInterface
{
    protected $queues;

    protected $defaultQueue;

    protected $backends;

    /**
     *
     * @param array  $queues
     * @param string $defaultQueue
     * @param array  $backends
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
     * {@inheritDoc}
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * @param string $message
     * @param string $status
     *
     * @return \Liip\Monitor\Result\CheckResult
     */
    protected function buildResult($message, $status)
    {
        return new CheckResult("backend health check", $message, $status);
    }
}
