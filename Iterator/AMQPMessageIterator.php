<?php

/*
 * This file is part of the Sonata project.
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
    protected $channel;

    protected $message;

    protected $AMQMessage;

    protected $queue;

    protected $counter;

    /**
     * @param \PhpAmqpLib\Channel\AMQPChannel $channel
     * @param $queue
     */
    public function __construct(AMQPChannel $channel, $queue)
    {
        $this->channel = $channel;
        $this->queue   = $queue;
        $this->counter = 0;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->message;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->wait();
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        $this->counter;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return count($this->channel->callbacks);
    }

    /**
     * {@inheritDoc}
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
            array($this, 'receiveMessage'
        ));

        $this->wait();

        return $this->message;
    }

    protected function wait()
    {
        while ($this->valid()) {
            $this->channel->wait();

            break;
        }
    }

    /**
     * @param  \PhpAmqpLib\Message\AMQPMessage $AMQMessage
     * @return void
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

        $this->counter++;

        $this->message= $message;
    }
}
