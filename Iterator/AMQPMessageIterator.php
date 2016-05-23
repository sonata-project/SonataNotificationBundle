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

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPMessageIterator implements MessageIteratorInterface
{
    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var mixed
     */
    protected $message;

    /**
     * @var AMQPMessage
     */
    protected $AMQMessage;

    /**
     * @var mixed
     */
    protected $queue;

    /**
     * @var int
     */
    protected $counter;

    /**
     * @param AMQPChannel $channel
     * @param mixed       $queue
     */
    public function __construct(AMQPChannel $channel, $queue)
    {
        $this->channel = $channel;
        $this->queue = $queue;
        $this->counter = 0;
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
        $this->wait();
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
        return count($this->channel->callbacks);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->channel->basic_consume(
            $this->queue,
            'sonata_notification_'.uniqid(),
            false,
            false,
            false,
            false,
            array($this, 'receiveMessage',
        ));

        $this->wait();

        return $this->message;
    }

    /**
     * @param AMQPMessage $AMQMessage
     */
    public function receiveMessage(AMQPMessage $AMQMessage)
    {
        $this->AMQMessage = $AMQMessage;

        $data = json_decode($this->AMQMessage->body, true);

        $message = new \Sonata\NotificationBundle\Model\Message();
        $data['body']['AMQMessage'] = $AMQMessage;
        $message->setBody($data['body']);
        $message->setType($data['type']);
        $message->setState($data['state']);

        ++$this->counter;

        $this->message = $message;
    }

    protected function wait()
    {
        while ($this->valid()) {
            $this->channel->wait();

            break;
        }
    }
}
