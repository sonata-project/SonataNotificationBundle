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

use Enqueue\AmqpTools\DelayStrategyAware;
use Enqueue\AmqpTools\RabbitMqDlxDelayStrategy;
use Guzzle\Http\Client as GuzzleClient;
use Interop\Amqp\AmqpConnectionFactory;
use Interop\Amqp\AmqpContext;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use Sonata\NotificationBundle\Exception\BackendNotFoundException;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

/**
 * Producer side of the rabbitmq backend.
 */
class AMQPBackendDispatcher extends QueueBackendDispatcher
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @var AMQPConnection
     */
    protected $connection;

    protected $backendsInitialized = false;

    /**
     * @var AmqpConnectionFactory
     */
    private $connectionFactory;

    /**
     * @var AmqpContext
     */
    private $context;

    /**
     * @param array  $settings
     * @param array  $queues
     * @param string $defaultQueue
     * @param array  $backends
     */
    public function __construct(array $settings, array $queues, $defaultQueue, array $backends)
    {
        parent::__construct($queues, $defaultQueue, $backends);

        $this->settings = $settings;
    }

    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @return AMQPChannel
     */
    public function getChannel()
    {
        @trigger_error(sprintf('The method %s is deprecated since version 3.3 and will be removed in 4.0. Use %s::getContext() instead.', __METHOD__, __CLASS__), E_USER_DEPRECATED);

        if (!$this->channel) {
            if (!$this->context instanceof \Enqueue\AmqpLib\AmqpContext) {
                throw new \LogicException('The BC layer works only if enqueue/amqp-lib lib is being used.');
            }

            // load context
            $this->getContext();

            /** @var \Enqueue\AmqpLib\AmqpContext $context */
            $context = $this->getContext();

            $this->channel = $context->getLibChannel();
            $this->connection = $this->channel->getConnection();
        }

        return $this->channel;
    }

    /**
     * @return AmqpContext
     */
    final public function getContext()
    {
        if (!$this->context) {
            if (!\array_key_exists('factory_class', $this->settings)) {
                throw new \LogicException('The factory_class option is missing though it is required.');
            }
            $factoryClass = $this->settings['factory_class'];
            if (
                !class_exists($factoryClass) ||
                !(new \ReflectionClass($factoryClass))->implementsInterface(AmqpConnectionFactory::class)
            ) {
                throw new \LogicException(sprintf(
                    'The factory_class option "%s" has to be valid class that implements "%s"',
                    $factoryClass,
                    AmqpConnectionFactory::class
                ));
            }

            /* @var AmqpConnectionFactory $factory */
            $this->connectionFactory = $factory = new $factoryClass([
                'host' => $this->settings['host'],
                'port' => $this->settings['port'],
                'user' => $this->settings['user'],
                'pass' => $this->settings['pass'],
                'vhost' => $this->settings['vhost'],
            ]);

            if ($factory instanceof DelayStrategyAware) {
                $factory->setDelayStrategy(new RabbitMqDlxDelayStrategy());
            }

            $this->context = $factory->createContext();

            register_shutdown_function([$this, 'shutdown']);
        }

        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend($type)
    {
        if (!$this->backendsInitialized) {
            foreach ($this->backends as $backend) {
                $backend['backend']->initialize();
            }
            $this->backendsInitialized = true;
        }

        $default = null;

        if (0 === \count($this->queues)) {
            foreach ($this->backends as $backend) {
                if ('default' === $backend['type']) {
                    return $backend['backend'];
                }
            }
        }

        foreach ($this->backends as $backend) {
            if ('all' === $type && '' === $backend['type']) {
                return $backend['backend'];
            }

            if ($backend['type'] === $type) {
                return $backend['backend'];
            }

            if ($backend['type'] === $this->defaultQueue) {
                $default = $backend['backend'];
            }
        }

        if (null === $default) {
            throw new BackendNotFoundException('Could not find a message backend for the type '.$type);
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        throw new \RuntimeException(
            'You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        throw new \RuntimeException(
            'You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        try {
            $this->getContext();
            $output = $this->getApiQueueStatus();
            $checked = 0;
            $missingConsumers = [];

            foreach ($this->queues as $queue) {
                foreach ($output as $q) {
                    if ($q['name'] === $queue['queue']) {
                        ++$checked;
                        if (0 === $q['consumers']) {
                            $missingConsumers[] = $queue['queue'];
                        }
                    }
                }
            }

            if ($checked !== \count($this->queues)) {
                return new Failure(
                    'Not all queues for the available notification types registered in the rabbitmq broker. '
                    .'Are the consumer commands running?'
                );
            }

            if (\count($missingConsumers) > 0) {
                return new Failure(
                    'There are no rabbitmq consumers running for the queues: '.implode(', ', $missingConsumers)
                );
            }
        } catch (\Exception $e) {
            return new Failure($e->getMessage());
        }

        return new Success('Channel is running (RabbitMQ) and consumers for all queues available.');
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
        throw new \RuntimeException(
            'You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.'
        );
    }

    public function shutdown()
    {
        if ($this->context) {
            $this->context->close();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
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
        if (!class_exists(GuzzleClient::class)) {
            throw new \RuntimeException(
                'The guzzle http client library is required to run rabbitmq health checks. '
                .'Make sure to add guzzlehttp/guzzle to your composer.json.'
            );
        }

        $client = new GuzzleClient();
        $client->setConfig(['curl.options' => [CURLOPT_CONNECTTIMEOUT_MS => 3000]]);
        $request = $client->get(sprintf('%s/queues', $this->settings['console_url']));
        $request->setAuth($this->settings['user'], $this->settings['pass']);

        return json_decode($request->send()->getBody(true), true);
    }
}
