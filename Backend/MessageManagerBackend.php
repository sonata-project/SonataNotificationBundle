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

    protected $checkLevel;

    /**
     * @param \Sonata\NotificationBundle\Model\MessageManagerInterface $messageManager
     * @param array $checkLevel
     */
    public function __construct(MessageManagerInterface $messageManager, array $checkLevel)
    {
        $this->messageManager = $messageManager;
        $this->checkLevel = $checkLevel;
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

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        try {
            $states = $this->messageManager->countStates();
        } catch (\Exception $e) {
            return new BackendStatus(BackendStatus::FAILURE, sprintf('Unable to retrieve message information - %s (Database)', $e->getMessage()));
        }

        if ($states[MessageInterface::STATE_IN_PROGRESS] > $this->checkLevel[MessageInterface::STATE_IN_PROGRESS]) {
            return new BackendStatus(BackendStatus::FAILURE, 'Too many messages processed at the same time (Database)');
        }

        if ($states[MessageInterface::STATE_ERROR] > $this->checkLevel[MessageInterface::STATE_ERROR]) {
            return new BackendStatus(BackendStatus::FAILURE, 'Too many errors (Database)');
        }

        if ($states[MessageInterface::STATE_OPEN] > $this->checkLevel[MessageInterface::STATE_OPEN]) {
            return new BackendStatus(BackendStatus::CRITICAL, 'Too many messages waiting to be processed (Database)');
        }

        if ($states[MessageInterface::STATE_DONE] > $this->checkLevel[MessageInterface::STATE_DONE]) {
            return new BackendStatus(BackendStatus::CRITICAL, 'Too many processed messages, please clean the database (Database)');
        }

        return new BackendStatus(BackendStatus::SUCCESS, 'Ok (Database)');
    }
}