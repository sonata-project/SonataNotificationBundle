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

use Interop\Amqp\AmqpConsumer;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpMessage;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind;
use PhpAmqpLib\Channel\AMQPChannel;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Exception\HandlingException;
use Sonata\NotificationBundle\Iterator\AMQPMessageIterator;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;

/**
 * Consumer side of the rabbitMQ backend.
 */
class AMQPBackend implements BackendInterface
{
    /**
     * @var AMQPBackendDispatcher
     */
    protected $dispatcher = null;

    /**
     * @var string
     */
    protected $exchange;

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var string
     */
    protected $recover;

    /**
     * @var null|string
     */
    protected $deadLetterExchange;

    /**
     * @var null|string
     */
    protected $deadLetterRoutingKey;

    /**
     * @var null|int
     */
    protected $ttl;

    /**
     * @var null|int
     */
    private $prefetchCount;

    /**
     * @var AmqpConsumer
     */
    private $consumer;

    /**
     * @param string   $exchange
     * @param string   $queue
     * @param string   $recover
     * @param string   $key
     * @param string   $deadLetterExchange
     * @param string   $deadLetterRoutingKey
     * @param null|int $ttl
     */
    public function __construct($exchange, $queue, $recover, $key, $deadLetterExchange = null, $deadLetterRoutingKey = null, $ttl = null, $prefetchCount = null)
    {
        $this->exchange = $exchange;
        $this->queue = $queue;
        $this->recover = $recover;
        $this->key = $key;
        $this->deadLetterExchange = $deadLetterExchange;
        $this->deadLetterRoutingKey = $deadLetterRoutingKey;
        $this->ttl = $ttl;
        $this->prefetchCount = $prefetchCount;
    }

    /**
     * @param AMQPBackendDispatcher $dispatcher
     */
    public function setDispatcher(AMQPBackendDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $args = [];
        if (null !== $this->deadLetterExchange) {
            $args['x-dead-letter-exchange'] = $this->deadLetterExchange;

            if (null !== $this->deadLetterRoutingKey) {
                $args['x-dead-letter-routing-key'] = $this->deadLetterRoutingKey;
            }
        }

        if (null !== $this->ttl) {
            $args['x-message-ttl'] = $this->ttl;
        }

        $queue = $this->getContext()->createQueue($this->queue);
        $queue->addFlag(AmqpQueue::FLAG_DURABLE);
        $queue->setArguments($args);
        $this->getContext()->declareQueue($queue);

        $topic = $this->getContext()->createTopic($this->exchange);
        $topic->setType(AmqpTopic::TYPE_DIRECT);
        $topic->addFlag(AmqpTopic::FLAG_DURABLE);
        $this->getContext()->declareTopic($topic);

        $this->getContext()->bind(new AmqpBind($queue, $topic, $this->key));

        if (null !== $this->deadLetterExchange && null === $this->deadLetterRoutingKey) {
            $deadLetterTopic = $this->getContext()->createTopic($this->deadLetterExchange);
            $deadLetterTopic->setType(AmqpTopic::TYPE_DIRECT);
            $deadLetterTopic->addFlag(AmqpTopic::FLAG_DURABLE);
            $this->getContext()->declareTopic($deadLetterTopic);

            $this->getContext()->bind(new AmqpBind($queue, $deadLetterTopic, $this->key));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        $body = json_encode([
            'type' => $message->getType(),
            'body' => $message->getBody(),
            'createdAt' => $message->getCreatedAt()->format('U'),
            'state' => $message->getState(),
        ]);

        $amqpMessage = $this->getContext()->createMessage($body);
        $amqpMessage->setContentType('text/plain'); // application/json ?
        $amqpMessage->setTimestamp($message->getCreatedAt()->format('U'));
        $amqpMessage->setDeliveryMode(AmqpMessage::DELIVERY_MODE_PERSISTENT);
        $amqpMessage->setRoutingKey($this->key);

        $topic = $this->getContext()->createTopic($this->exchange);

        $this->getContext()->createProducer()->send($topic, $amqpMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function create($type, array $body)
    {
        $message = new Message();
        $message->setType($type);
        $message->setBody($body);
        $message->setState(MessageInterface::STATE_OPEN);

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function createAndPublish($type, array $body)
    {
        $this->publish($this->create($type, $body));
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $context = $this->getContext();

        if (null !== $this->prefetchCount) {
            $context->setQos(null, $this->prefetchCount, false);
        }

        $this->consumer = $this->getContext()->createConsumer($this->getContext()->createQueue($this->queue));
        $this->consumer->setConsumerTag('sonata_notification_'.uniqid());

        return new AMQPMessageIterator($this->getChannel(), $this->consumer);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        $event = new ConsumerEvent($message);

        /** @var AmqpMessage $amqpMessage */
        $amqpMessage = $message->getValue('interopMessage');

        try {
            $dispatcher->dispatch($message->getType(), $event);

            $this->consumer->acknowledge($amqpMessage);

            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_DONE);
        } catch (HandlingException $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            $this->consumer->acknowledge($amqpMessage);

            throw new HandlingException('Error while handling a message', 0, $e);
        } catch (\Exception $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            $this->consumer->reject($amqpMessage, $this->recover);

            throw new HandlingException('Error while handling a message', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        try {
            $this->getContext();
        } catch (\Exception $e) {
            return new Failure($e->getMessage());
        }

        return new Success('Channel is running (RabbitMQ)');
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @return AMQPChannel
     */
    protected function getChannel()
    {
        if (null === $this->dispatcher) {
            throw new \RuntimeException('Unable to retrieve AMQP channel without dispatcher.');
        }

        return $this->dispatcher->getChannel();
    }

    /**
     * @return AmqpContext
     */
    private function getContext()
    {
        if (null === $this->dispatcher) {
            throw new \RuntimeException('Unable to retrieve AMQP context without dispatcher.');
        }

        return $this->dispatcher->getContext();
    }
}
