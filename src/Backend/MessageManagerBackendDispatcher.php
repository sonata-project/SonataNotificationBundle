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

namespace Sonata\NotificationBundle\Backend;

use Laminas\Diagnostics\Result\Success;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Producer side of the doctrine backend.
 */
class MessageManagerBackendDispatcher extends QueueBackendDispatcher
{
    /**
     * @var array
     */
    protected $dedicatedTypes = [];

    /**
     * @var BackendInterface
     */
    protected $default;

    /**
     * @param MessageManagerInterface $messageManager Only used in compiler pass
     * @param string                  $defaultQueue
     */
    public function __construct(MessageManagerInterface $messageManager, array $queues, $defaultQueue, array $backends)
    {
        parent::__construct($queues, $defaultQueue, $backends);

        foreach ($this->queues as $queue) {
            if (true === $queue['default']) {
                continue;
            }

            $this->dedicatedTypes = array_merge($this->dedicatedTypes, $queue['types']);
        }

        foreach ($this->backends as $backend) {
            if (empty($backend['types'])) {
                $this->default = $backend['backend'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend($type)
    {
        $default = null;

        if (!$type) {
            return $this->getDefaultBackend();
        }

        foreach ($this->backends as $backend) {
            if (\in_array($type, $backend['types'], true)) {
                return $backend['backend'];
            }
        }

        return $this->getDefaultBackend();
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): void
    {
        throw new \RuntimeException('You need to use a specific doctrine backend supporting the selected queue to run a consumer.');
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher): void
    {
        throw new \RuntimeException('You need to use a specific doctrine backend supporting the selected queue to run a consumer.');
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return new Success('Channel is running (Database) and consumers for all queues available.');
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup(): void
    {
        throw new \RuntimeException('You need to use a specific doctrine backend supporting the selected queue to run a consumer.');
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
    }

    /**
     * @return BackendInterface
     */
    protected function getDefaultBackend()
    {
        $types = [];

        if (!empty($this->dedicatedTypes)) {
            $types = [
                'exclude' => $this->dedicatedTypes,
            ];
        }

        $this->default->setTypes($types);

        return $this->default;
    }
}
