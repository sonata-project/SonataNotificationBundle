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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Exception\HandlingException;

use Liip\Monitor\Result\CheckResult;

class RuntimeBackend implements BackendInterface
{
    protected $dispatcher;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        $this->handle($message, $this->dispatcher);

        return $message;
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
        return new \EmptyIterator();
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
    public function cleanup()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        $event = new ConsumerEvent($message);

        try {
            $dispatcher->dispatch($message->getType(), $event);

            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_DONE);

        } catch (\Exception $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            throw new HandlingException("Error while handling a message: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
         return new CheckResult('Runtime backend health check', 'Ok  (Runtime)', CheckResult::OK);
    }
}
