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
use Sonata\NotificationBundle\Exception\BackendNotFoundException;

use PhpAmqpLib\Connection\AMQPConnection;

use Liip\Monitor\Result\CheckResult;

/**
 * Producer side of the rabbitmq backend.
 */
class AMQPBackendDispatcher extends QueueBackendDispatcher
{
    protected $settings;

    protected $channel;

    protected $connection;

    /**
     * @param array   $settings
     * @param array   $queues
     * @param string  $defaultQueue
     * @param array   $backends
     */
    public function __construct(array $settings, array $queues, $defaultQueue, array $backends)
    {
        parent::__construct($queues, $defaultQueue, $backends);

        $this->settings = $settings;
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

        foreach ($this->backends as $backend) {
            if ('all' === $type && $backend['type'] === '') {
                return $backend['backend'];
            }

            if ($backend['type'] === $type) {
                return $backend['backend'];
            }

            if ($backend['type'] === $this->defaultQueue) {
                $default = $backend['backend'];
            }
        }

        if ($default === null) {
            throw new BackendNotFoundException('Could not find a message backend for the type ' . $type);
        }

        return $default;
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
     * {@inheritdoc}
     */
    public function initialize() {}
}
