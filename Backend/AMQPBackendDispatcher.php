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

use Liip\Monitor\Result\CheckResult;

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
     * @param array   $settings
     * @param array   $queues
     * @param unknown $defaultQueue
     * @param array   $backends
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
            $output = $this->getApiQueueStatus();
            $checked = 0;
            $missingConsumers = array();

            foreach ($this->queues as $queue) {
                foreach ($output as $q) {
                    if ($q['name'] === $queue['queue']) {
                        $checked++;
                        if ($q['consumers'] === 0) {
                            $missingConsumers[] = $queue['queue'];
                        }
                    }
                }
            }

            if ($checked !== count($this->queues)) {
                return $this->buildResult('Not all queues for the available notification types registered in the rabbitmq broker. Are the consumer commands running?', CheckResult::CRITICAL);
            }

            if (count($missingConsumers) > 0) {
                return $this->buildResult('There are no rabbitmq consumers running for the queues: '. implode(', ', $missingConsumers), CheckResult::CRITICAL);
            }

        } catch (\Exception $e) {
            return $this->buildResult($e->getMessage(), CheckResult::CRITICAL);
        }

        return $this->buildResult('Channel is running (RabbitMQ) and consumers for all queues available.', CheckResult::OK);
    }

    /**
     * Calls the rabbitmq management api /api/<vhost>/queues endpoint to list the available queues.
     *
     * @see http://hg.rabbitmq.com/rabbitmq-management/raw-file/3646dee55e02/priv/www-api/help.html
     *
     * @return array
     */
    protected function getApiQueueStatus()
    {
        if (class_exists('Guzzle\Http\Client') === false) {
            throw new \RuntimeException("The guzzle http client library is required to run rabbitmq health checks. Make sure to add guzzle/guzzle to your composer.json.");
        }

        $client = new \Guzzle\Http\Client();
        $client->setConfig(array('curl.options' => array(CURLOPT_CONNECTTIMEOUT_MS => 3000)));
        $request = $client->get(sprintf('%s/queues', $this->settings['console_url']));
        $request->setAuth($this->settings['user'], $this->settings['pass']);
        $response = $request->send();

        return json_decode($request->send()->getBody(true), true);
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

    /**
     * @param  string                           $message
     * @param  string                           $status
     * @return \Liip\Monitor\Result\CheckResult
     */
    protected function buildResult($message, $status)
    {
        return new CheckResult("Rabbitmq backend health check", $message, $status);
    }
}
