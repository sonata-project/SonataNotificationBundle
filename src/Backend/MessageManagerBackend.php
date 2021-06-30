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

namespace Sonata\NotificationBundle\Backend;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Exception\HandlingException;
use Sonata\NotificationBundle\Iterator\MessageManagerMessageIterator;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @final since sonata-project/notification-bundle 3.13
 */
class MessageManagerBackend implements BackendInterface
{
    /**
     * @var MessageManagerInterface
     */
    protected $messageManager;

    /**
     * @var array
     */
    protected $checkLevel;

    /**
     * @var int
     */
    protected $pause;

    /**
     * @var int
     */
    protected $maxAge;

    /**
     * @var MessageManagerBackendDispatcher|null
     */
    protected $dispatcher = null;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @param int $pause
     * @param int $maxAge
     * @param int $batchSize
     */
    public function __construct(MessageManagerInterface $messageManager, array $checkLevel, $pause = 500000, $maxAge = 86400, $batchSize = 10, array $types = [])
    {
        $this->messageManager = $messageManager;
        $this->checkLevel = $checkLevel;
        $this->pause = $pause;
        $this->maxAge = $maxAge;
        $this->batchSize = $batchSize;
        $this->types = $types;
    }

    /**
     * @param array $types
     */
    public function setTypes($types): void
    {
        $this->types = $types;
    }

    public function publish(MessageInterface $message)
    {
        $this->messageManager->save($message);

        return $message;
    }

    public function create($type, array $body)
    {
        $message = $this->messageManager->create();
        $message->setType($type);
        $message->setBody($body);
        $message->setState(MessageInterface::STATE_OPEN);

        return $message;
    }

    public function createAndPublish($type, array $body)
    {
        return $this->publish($this->create($type, $body));
    }

    public function getIterator()
    {
        return new MessageManagerMessageIterator($this->messageManager, $this->types, $this->pause, $this->batchSize);
    }

    public function initialize(): void
    {
    }

    public function setDispatcher(MessageManagerBackendDispatcher $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        $event = new ConsumerEvent($message);

        try {
            $message->setStartedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_IN_PROGRESS);
            $this->messageManager->save($message);

            $dispatcher->dispatch($event, $message->getType());

            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_DONE);
            $this->messageManager->save($message);

            return $event->getReturnInfo();
        } catch (\Exception $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            $this->messageManager->save($message);

            throw new HandlingException('Error while handling a message', 0, $e);
        }
    }

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

    public function cleanup(): void
    {
        $this->messageManager->cleanup($this->maxAge);
    }
}
