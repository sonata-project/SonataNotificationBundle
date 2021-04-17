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

use Laminas\Diagnostics\Result\Success;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Exception\HandlingException;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuntimeBackend implements BackendInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function publish(MessageInterface $message)
    {
        $this->handle($message, $this->dispatcher);

        return $message;
    }

    public function create($type, array $body)
    {
        $message = new Message();
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
        return new \EmptyIterator();
    }

    public function initialize()
    {
    }

    public function cleanup()
    {
    }

    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        $event = new ConsumerEvent($message);

        try {
            $dispatcher->dispatch($event, $message->getType());

            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_DONE);
        } catch (\Exception $e) {
            $message->setCompletedAt(new \DateTime());
            $message->setState(MessageInterface::STATE_ERROR);

            throw new HandlingException('Error while handling a message: '.$e->getMessage(), 0, $e);
        }
    }

    public function getStatus()
    {
        return new Success('Runtime backend health check', 'Ok  (Runtime)');
    }
}
