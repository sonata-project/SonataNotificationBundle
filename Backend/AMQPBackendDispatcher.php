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
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Exception\QueueNotFoundException;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Producer side of the rabbitmq backend.
 */
class AMQPBackendDispatcher implements QueueDispatcherInterface, BackendInterface
{
    protected $settings;

    protected $queues;

    protected $defaultQueue;

    protected $backends;

    protected $channel;

    protected $connection;

    /**
     * @param array $settings
     * @param array $queues
     * @param unknown $defaultQueue
     * @param array $backends
     */
    public function __construct(array $settings, array $queues, $defaultQueue, array $backends)
    {
        $this->settings = $settings;
        $this->queues = $queues;
        $this->backends = $backends;
        $this->defaultQueue = $defaultQueue;

        foreach ($this->backends as $backend) {
            $backend['backend']->setDispatcher($this);
        }
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel()
    {
        if (!$this->channel) {
            $this->connection = new AMQPConnection(
                $this->settings['host'],
                $this->settings['port'],
                $this->settings['user'],
                $this->settings['pass'],
                $this->settings['vhost']
            );

            $this->channel = $this->connection->channel();

            register_shutdown_function(array($this, 'shutdown'));
        }

        return $this->channel;
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
        $this->getBackend($type)->create($type, $body);
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
    public function getBackend($type)
    {
        $default = null;

        if (count($this->queues) === 0) {
            foreach ($this->backends as $backend) {
                if ($backend['type'] === 'default') {
                    return $backend['backend'];
                }
            }
        }

        foreach ($this->queues as $queue) {
            foreach ($this->backends as $backend) {
                if ($backend['type'] === $type) {
                    return $backend['backend'];
                }
                if ($backend['type'] === $this->defaultQueue) {
                    $default = $backend['backend'];
                }
            }
        }

        if ($default === null) {
            throw new QueueNotFoundException('Could not find a message backend for the type ' . $type);
        }

        return $default;
    }

    /**
     * {@inheritDoc}
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        throw new \RuntimeException('You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.');
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        throw new \RuntimeException('You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.');
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        try {
            $this->getChannel();
        } catch(\Exception $e) {
            return new BackendStatus(BackendStatus::CRITICAL, 'Error : '.$e->getMessage(). ' (RabbitMQ)');
        }

        return new BackendStatus(BackendStatus::OK, 'Channel is running (RabbitMQ)');
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
        throw new \RuntimeException('You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.');
    }

    /**
     * @return void
     */
    public function shutdown()
    {
        if ($this->channel) {
            $this->channel->close();
        }

        if ($this->connection) {
            $this->connection->close();
        }
    }
}
