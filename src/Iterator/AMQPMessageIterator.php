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

namespace Sonata\NotificationBundle\Iterator;

use Interop\Amqp\AmqpConsumer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Sonata\NotificationBundle\Model\Message;

class AMQPMessageIterator implements MessageIteratorInterface
{
    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var mixed
     */
    protected $message;

    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @var AMQPMessage
     */
    protected $AMQMessage;

    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @var string
     */
    protected $queue;

    /**
     * @var int
     */
    protected $counter;

    /**
     * @var \Interop\Amqp\AmqpMessage
     */
    private $interopMessage;

    /**
     * @var int
     */
    private $timeout;

    /**
     * @var AmqpConsumer
     */
    private $consumer;

    /**
     * @var bool
     */
    private $isValid;

    public function __construct(AMQPChannel $channel, AmqpConsumer $consumer)
    {
        $this->consumer = $consumer;
        $this->counter = 0;
        $this->timeout = 0;
        $this->isValid = true;

        $this->channel = $channel;
        $this->queue = $consumer->getQueue()->getQueueName();
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        $this->isValid = false;

        if ($amqpMessage = $this->consumer->receive($this->timeout)) {
            $this->AMQMessage = $this->convertToAmqpLibMessage($amqpMessage);

            $data = json_decode($amqpMessage->getBody(), true);
            $data['body']['interopMessage'] = $amqpMessage;

            // @deprecated
            $data['body']['AMQMessage'] = $this->AMQMessage;

            $message = new Message();
            $message->setBody($data['body']);
            $message->setType($data['type']);
            $message->setState($data['state']);
            $this->message = $message;

            ++$this->counter;
            $this->isValid = true;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key(): void
    {
        $this->counter;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->isValid;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->isValid = true;
        $this->next();
    }

    /**
     * @deprecated since 3.2, will be removed in 4.x
     *
     * @param \Interop\Amqp\AmqpMessage $amqpMessage
     *
     * @return AMQPMessage
     */
    private function convertToAmqpLibMessage(\Interop\Amqp\AmqpMessage $amqpMessage)
    {
        $amqpLibProperties = $amqpMessage->getHeaders();
        $amqpLibProperties['application_headers'] = $amqpMessage->getProperties();

        $amqpLibMessage = new AMQPMessage($amqpMessage->getBody(), $amqpLibProperties);
        $amqpLibMessage->delivery_info = [
            'consumer_tag' => $this->consumer->getConsumerTag(),
            'delivery_tag' => $amqpMessage->getDeliveryTag(),
            'redelivered' => $amqpMessage->isRedelivered(),
            'routing_key' => $amqpMessage->getRoutingKey(),
            'channel' => $this->channel,
        ];

        return $amqpLibMessage;
    }
}
