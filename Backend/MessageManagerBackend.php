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
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Sonata\NotificationBundle\Iterator\MessageManagerMessageIterator;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Exception\HandlingException;

class MessageManagerBackend implements BackendInterface
{
    protected $messageManager;

    /**
     * @param \Sonata\NotificationBundle\Model\MessageManagerInterface $messageManager
     */
    public function __construct(MessageManagerInterface $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        $this->messageManager->save($message);

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function create($type, array $body)
    {
        $message = $this->messageManager->create();
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
        return new MessageManagerMessageIterator($this->messageManager);
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
        $event = new ConsumerEvent($message);

        try {
            $message->setStartedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_IN_PROGRESS);
            $this->messageManager->save($message);

            $dispatcher->dispatch($message->getType(), $event);

            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_DONE);
            $this->messageManager->save($message);

        } catch(\Exception $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            $this->messageManager->save($message);

            throw new HandlingException("Error while handling a message", 0, $e);
        }
    }
}