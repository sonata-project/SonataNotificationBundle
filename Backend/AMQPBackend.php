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

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
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
     * @var AMQPBackendDispatcher
     */
    protected $dispatcher = null;

    /**
     * @param string   $exchange
     * @param string   $queue
     * @param string   $recover
     * @param string   $key
     * @param string   $deadLetterExchange
     * @param string   $deadLetterRoutingKey
     * @param null|int $ttl
     */
    public function __construct($exchange, $queue, $recover, $key, $deadLetterExchange = null, $deadLetterRoutingKey = null, $ttl = null)
    {
        $this->exchange = $exchange;
        $this->queue = $queue;
        $this->recover = $recover;
        $this->key = $key;
        $this->deadLetterExchange = $deadLetterExchange;
        $this->deadLetterRoutingKey = $deadLetterRoutingKey;
        $this->ttl = $ttl;

        if (!class_exists('PhpAmqpLib\Message\AMQPMessage')) {
            throw new \RuntimeException('Please install php-amqplib/php-amqplib dependency');
        }
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
        $args = array();

        if ($this->deadLetterExchange !== null) {
            $args['x-dead-letter-exchange'] = array('S', $this->deadLetterExchange);

            if ($this->deadLetterRoutingKey !== null) {
                $args['x-dead-letter-routing-key'] = array('S', $this->deadLetterRoutingKey);
            }
        }

        if ($this->ttl !== null) {
            $args['x-message-ttl'] = array('I', $this->ttl);
        }

        /*
         * name: $queue
         * passive: false
         * durable: true // the queue will survive server restarts
         * exclusive: false // the queue can be accessed in other channels
         * auto_delete: false //the queue won't be deleted once the channel is closed.
         * no_wait: false the channel will wait until queue.declare_ok is received
         * args: array
         */
        $this->getChannel()->queue_declare($this->queue, false, true, false, false, false, $args);

        /*
         * name: $exchange
         * type: direct
         * passive: false
         * durable: true // the exchange will survive server restarts
         * auto_delete: false //the exchange won't be deleted once the channel is closed.
         **/
        $this->getChannel()->exchange_declare($this->exchange, 'direct', false, true, false);

        $this->getChannel()->queue_bind($this->queue, $this->exchange, $this->key);

        if ($this->deadLetterExchange !== null && $this->deadLetterRoutingKey === null) {
            $this->getChannel()->exchange_declare($this->deadLetterExchange, 'direct', false, true, false);
            $this->getChannel()->queue_bind($this->queue, $this->deadLetterExchange, $this->key);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        $body = json_encode(array(
            'type' => $message->getType(),
            'body' => $message->getBody(),
            'createdAt' => $message->getCreatedAt()->format('U'),
            'state' => $message->getState(),
        ));

        $amq = new AMQPMessage($body, array(
            'content_type' => 'text/plain',
            'delivery_mode' => 2,
        ));

        $this->getChannel()->basic_publish($amq, $this->exchange, $this->key);
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
        return new AMQPMessageIterator($this->getChannel(), $this->queue);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        $event = new ConsumerEvent($message);

        try {
            $dispatcher->dispatch($message->getType(), $event);

            $message->getValue('AMQMessage')->delivery_info['channel']->basic_ack($message->getValue('AMQMessage')->delivery_info['delivery_tag']);

            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_DONE);
        } catch (HandlingException $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            $message->getValue('AMQMessage')->delivery_info['channel']->basic_ack($message->getValue('AMQMessage')->delivery_info['delivery_tag']);

            throw new HandlingException('Error while handling a message', 0, $e);
        } catch (\Exception $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            if ($this->recover === true) {
                $message->getValue('AMQMessage')->delivery_info['channel']->basic_recover($message->getValue('AMQMessage')->delivery_info['delivery_tag']);
            } elseif ($this->deadLetterExchange !== null) {
                $message->getValue('AMQMessage')->delivery_info['channel']->basic_reject($message->getValue('AMQMessage')->delivery_info['delivery_tag'], false);
            }

            throw new HandlingException('Error while handling a message', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        try {
            $this->getChannel();
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
     * @return AMQPChannel
     */
    protected function getChannel()
    {
        if ($this->dispatcher === null) {
            throw new \RuntimeException('Unable to retrieve AMQP channel without dispatcher.');
        }

        return $this->dispatcher->getChannel();
    }
}
