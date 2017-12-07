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
use Sonata\NotificationBundle\Backend\PostponeRuntimeBackend;
use Sonata\NotificationBundle\Consumer\ConsumerEventInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @covers \Sonata\NotificationBundle\Backend\PostponeRuntimeBackend
 */
class PostponeRuntimeBackendTest extends TestCase
{
    public function testIteratorContainsPublishedMessages(): void
    {
        $backend = new PostponeRuntimeBackend(
            $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'),
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

    public function testNoMessagesOnEvent(): void
    {
        $backend = $this->getMockBuilder('Sonata\NotificationBundle\Backend\PostponeRuntimeBackend')
            ->setMethods(['handle'])
            ->setConstructorArgs([$this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')])
            ->getMock();

        $backend
            ->expects($this->never())
            ->method('handle')
        ;

        $backend->onEvent();
    }

    public function testLiveEnvironment(): void
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
        $dispatcher->addListener('notification.demo', function (ConsumerEventInterface $event) use ($phpunit, $message): void {
            $phpunit->assertSame($message, $event->getMessage());

            $phpunit->passed = true;
        });

        $dispatcher->dispatch('kernel.terminate');

        $this->assertTrue($phpunit->passed);
        $this->assertEquals(MessageInterface::STATE_DONE, $message->getState());
    }

    public function testRecursiveMessage(): void
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

        $dispatcher->addListener('notification.demo1', function (ConsumerEventInterface $event) use ($phpunit, $message1, $message2, $backend, $dispatcher): void {
            $phpunit->assertSame($message1, $event->getMessage());

            $phpunit->passed1 = true;

            $backend->publish($message2);
        });

        $dispatcher->addListener('notification.demo2', function (ConsumerEventInterface $event) use ($phpunit, $message2): void {
            $phpunit->assertSame($message2, $event->getMessage());

            $phpunit->passed2 = true;
        });

        $dispatcher->dispatch('kernel.terminate');

        $this->assertTrue($phpunit->passed1);
        $this->assertTrue($phpunit->passed2);

        $this->assertEquals(MessageInterface::STATE_DONE, $message1->getState());
        $this->assertEquals(MessageInterface::STATE_DONE, $message2->getState());
    }

    public function testStatusIsOk(): void
    {
        if (!class_exists('ZendDiagnostics\Result\Success')) {
            $this->markTestSkipped('The class ZendDiagnostics\Result\Success does not exist');
        }

        $backend = new PostponeRuntimeBackend(
            $this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'),
            true
        );

        $status = $backend->getStatus();
        $this->assertInstanceOf('ZendDiagnostics\Result\Success', $status);
    }

    public function testOnCliPublishHandlesDirectly(): void
    {
        $backend = $this->getMockBuilder('Sonata\NotificationBundle\Backend\PostponeRuntimeBackend')
            ->setMethods(['handle'])
            ->setConstructorArgs([$this->createMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')])
            ->getMock();

        $backend
            ->expects($this->once())
            ->method('handle')
        ;

        $message = $backend->create('notification.demo', []);
        $backend->publish($message);
    }
}
