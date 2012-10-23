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

use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Exception\HandlingException;
use Sonata\NotificationBundle\Iterator\AMQPMessageIterator;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPBackend implements BackendInterface
{
    protected $settings;

    protected $exchange;

    protected $queue;

    protected $connection;

    protected $channel;

    protected $key;

    /**
     * @param array $settings
     * @param string $exchange
     * @param string $queue
     * @param string $key
     */
    public function __construct(array $settings, $exchange, $queue, $key)
    {
        $this->settings = $settings;
        $this->exchange = $exchange;
        $this->queue    = $queue;
        $this->key      = $key;
    }

    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    protected function getChannel()
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
    public function initialize()
    {
        /**
         * name: $queue
         * passive: false
         * durable: true // the queue will survive server restarts
         * exclusive: false // the queue can be accessed in other channels
         * auto_delete: false //the queue won't be deleted once the channel is closed.
         */
        $this->getChannel()->queue_declare($this->queue, false, true, false, false);

        /**
         * name: $exchange
         * type: direct
         * passive: false
         * durable: true // the exchange will survive server restarts
         * auto_delete: false //the exchange won't be deleted once the channel is closed.
         **/
       $this->getChannel()->exchange_declare($this->exchange, 'direct', false, true, false);

       $this->getChannel()->queue_bind($this->queue, $this->exchange, $this->key);
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
            'state' => $message->getState()
        ));

        $amq = new AMQPMessage($body, array(
            'content_type' => 'text/plain',
            'delivery-mode' => 2
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
        return $this->publish($this->create($type, $body));
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

        } catch(HandlingException $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            $message->getValue('AMQMessage')->delivery_info['channel']->basic_ack($message->getValue('AMQMessage')->delivery_info['delivery_tag']);

            throw new HandlingException("Error while handling a message", 0, $e);
        } catch(\Exception $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            throw new HandlingException("Error while handling a message", 0, $e);
        }
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
        throw new \RuntimeException('Not implemented');
    }
}
