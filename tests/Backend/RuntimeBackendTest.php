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

namespace Sonata\NotificationBundle\Tests\Backend;

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\RuntimeBackend;
use Sonata\NotificationBundle\Exception\HandlingException;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Tests\Entity\Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RuntimeBackendTest extends TestCase
{
    public function testCreateAndPublish(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $backend = new RuntimeBackend($dispatcher);
        $message = $backend->createAndPublish('foo', ['message' => 'salut']);

        static::assertInstanceOf(MessageInterface::class, $message);

        static::assertSame(MessageInterface::STATE_DONE, $message->getState());
        static::assertNotNull($message->getCreatedAt());
        static::assertSame('foo', $message->getType());
        static::assertSame(['message' => 'salut'], $message->getBody());
    }

    public function testIterator(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $backend = new RuntimeBackend($dispatcher);

        static::assertInstanceOf('Iterator', $backend->getIterator());
    }

    public function testHandleSuccess(): void
    {
        $message = new Message();

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(static::once())->method('dispatch');

        $backend = new RuntimeBackend($dispatcher);

        $backend->handle($message, $dispatcher);
        static::assertSame(MessageInterface::STATE_DONE, $message->getState());
        static::assertNotNull($message->getCreatedAt());
        static::assertNotNull($message->getCompletedAt());
    }

    public function testHandleError(): void
    {
        $message = new Message();

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects(static::once())->method('dispatch')->will(static::throwException(new \RuntimeException()));

        $backend = new RuntimeBackend($dispatcher);

        $e = false;

        try {
            $backend->handle($message, $dispatcher);
        } catch (HandlingException $e) {
        }

        static::assertInstanceOf(HandlingException::class, $e);

        static::assertSame(MessageInterface::STATE_ERROR, $message->getState());
        static::assertNotNull($message->getCreatedAt());
        static::assertNotNull($message->getCompletedAt());
    }
}
