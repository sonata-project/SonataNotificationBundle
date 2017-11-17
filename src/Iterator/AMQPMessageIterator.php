<?php

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
use Sonata\NotificationBundle\Model\Message;

final class AMQPMessageIterator implements MessageIteratorInterface
{
    /**
     * @var mixed
     */
    private $message;

    /**
     * @var int
     */
    private $counter;

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

    public function __construct(AmqpConsumer $consumer)
    {
        $this->consumer = $consumer;
        $this->counter = 0;
        $this->timeout = 0;
        $this->isValid = true;
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
    public function next()
    {
        $this->isValid = false;

        if ($amqpMessage = $this->consumer->receive($this->timeout)) {
            $data = json_decode($amqpMessage->getBody(), true);
            $data['body']['interopMessage'] = $amqpMessage;

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
    public function key()
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
    public function rewind()
    {
        $this->isValid = true;
        $this->next();
    }
}
