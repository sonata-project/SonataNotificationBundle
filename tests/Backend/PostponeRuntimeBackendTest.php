<?php

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
use Sonata\NotificationBundle\Backend\PostponeRuntimeBackend;
use Sonata\NotificationBundle\Consumer\ConsumerEventInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ZendDiagnostics\Result\Success;

/**
 * @covers \Sonata\NotificationBundle\Backend\PostponeRuntimeBackend
 */
class PostponeRuntimeBackendTest extends TestCase
{
    public function testIteratorContainsPublishedMessages()
    {
        $backend = new PostponeRuntimeBackend(
            $this->createMock(EventDispatcherInterface::class),
            true
        );

        $messages = [];

        $message = $backend->create('foo', []);
        $messages[] = $message;
        $backend->publish($message);

        $message = $backend->create('bar', []);
        $messages[] = $message;
        $backend->publish($message);

        $message = $backend->create('baz', []);
        $messages[] = $message;
        $backend->publish($message);

        $backend->create('not_published', []);

        $iterator = $backend->getIterator();
        foreach ($iterator as $eachKey => $eachMessage) {
            $this->assertSame($messages[$eachKey], $eachMessage);
        }
    }

    public function testNoMessagesOnEvent()
    {
        $backend = $this->getMockBuilder(PostponeRuntimeBackend::class)
            ->setMethods(['handle'])
            ->setConstructorArgs([$this->createMock(EventDispatcherInterface::class)])
            ->getMock();

        $backend
            ->expects($this->never())
            ->method('handle')
        ;

        $backend->onEvent();
    }

    public function testLiveEnvironment()
    {
        $dispatcher = new EventDispatcher();
        $backend = new PostponeRuntimeBackend($dispatcher, true);
        $dispatcher->addListener('kernel.terminate', [$backend, 'onEvent']);

        $message = $backend->create('notification.demo', []);
        $backend->publish($message);

        // This message will not be handled.
        $backend->create('notification.demo', []);

        $phpunit = $this;
        $phpunit->passed = false;
        $dispatcher->addListener('notification.demo', function (ConsumerEventInterface $event) use ($phpunit, $message) {
            $phpunit->assertSame($message, $event->getMessage());

            $phpunit->passed = true;
        });

        $dispatcher->dispatch('kernel.terminate');

        $this->assertTrue($phpunit->passed);
        $this->assertEquals(MessageInterface::STATE_DONE, $message->getState());
    }

    public function testRecursiveMessage()
    {
        $dispatcher = new EventDispatcher();
        $backend = new PostponeRuntimeBackend($dispatcher, true);
        $dispatcher->addListener('kernel.terminate', [$backend, 'onEvent']);

        $message1 = $backend->create('notification.demo1', []);
        $message2 = $backend->create('notification.demo2', []);

        $backend->publish($message1);

        $phpunit = $this;
        $phpunit->passed1 = false;
        $phpunit->passed2 = false;

        $dispatcher->addListener('notification.demo1', function (ConsumerEventInterface $event) use ($phpunit, $message1, $message2, $backend, $dispatcher) {
            $phpunit->assertSame($message1, $event->getMessage());

            $phpunit->passed1 = true;

            $backend->publish($message2);
        });

        $dispatcher->addListener('notification.demo2', function (ConsumerEventInterface $event) use ($phpunit, $message2) {
            $phpunit->assertSame($message2, $event->getMessage());

            $phpunit->passed2 = true;
        });

        $dispatcher->dispatch('kernel.terminate');

        $this->assertTrue($phpunit->passed1);
        $this->assertTrue($phpunit->passed2);

        $this->assertEquals(MessageInterface::STATE_DONE, $message1->getState());
        $this->assertEquals(MessageInterface::STATE_DONE, $message2->getState());
    }

    public function testStatusIsOk()
    {
        if (!class_exists(Success::class)) {
            $this->markTestSkipped('The class ZendDiagnostics\Result\Success does not exist');
        }

        $backend = new PostponeRuntimeBackend(
            $this->createMock(EventDispatcherInterface::class),
            true
        );

        $status = $backend->getStatus();
        $this->assertInstanceOf(Success::class, $status);
    }

    public function testOnCliPublishHandlesDirectly()
    {
        $backend = $this->getMockBuilder(PostponeRuntimeBackend::class)
            ->setMethods(['handle'])
            ->setConstructorArgs([$this->createMock(EventDispatcherInterface::class)])
            ->getMock();

        $backend
            ->expects($this->once())
            ->method('handle')
        ;

        $message = $backend->create('notification.demo', []);
        $backend->publish($message);
    }
}
