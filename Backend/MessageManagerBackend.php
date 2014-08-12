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

use ZendDiagnostics\Result\Failure;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;

class MessageManagerBackend implements BackendInterface
{
    protected $messageManager;

    protected $checkLevel;

    protected $pause;

    protected $maxAge;

    protected $dispatcher = null;

    protected $types;

    protected $batchSize;

    /**
     * @param MessageManagerInterface $messageManager
     * @param array                   $checkLevel
     * @param int                     $pause
     * @param int                     $maxAge
     * @param int                     $batchSize
     * @param array                   $types
     */
    public function __construct(MessageManagerInterface $messageManager, array $checkLevel, $pause = 500000, $maxAge = 86400, $batchSize = 10, array $types = array())
    {
        $this->messageManager = $messageManager;
        $this->checkLevel     = $checkLevel;
        $this->pause          = $pause;
        $this->maxAge         = $maxAge;
        $this->batchSize      = $batchSize;
        $this->types          = $types;
    }

    /**
     * @param array $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
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
        return new MessageManagerMessageIterator($this->messageManager, $this->types, $this->pause, $this->batchSize);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
    }

    /**
     * @param MessageManagerBackendDispatcher $dispatcher
     */
    public function setDispatcher(MessageManagerBackendDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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

            return $event->getReturnInfo();

        } catch (\Exception $e) {
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
            return new Failure(sprintf('Unable to retrieve message information - %s (Database)', $e->getMessage()));
        }

        if ($states[MessageInterface::STATE_IN_PROGRESS] > $this->checkLevel[MessageInterface::STATE_IN_PROGRESS]) {
            return new Failure('Too many messages processed at the same time (Database)');
        }

        if ($states[MessageInterface::STATE_ERROR] > $this->checkLevel[MessageInterface::STATE_ERROR]) {
            return new Failure('Too many errors (Database)');
        }

        if ($states[MessageInterface::STATE_OPEN] > $this->checkLevel[MessageInterface::STATE_OPEN]) {
            return new Warning('Too many messages waiting to be processed (Database)');
        }

        if ($states[MessageInterface::STATE_DONE] > $this->checkLevel[MessageInterface::STATE_DONE]) {
            return new Warning('Too many processed messages, please clean the database (Database)');
        }

        return new Success('Ok (Database)');
    }

    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
        $this->messageManager->cleanup($this->maxAge);
    }
}
