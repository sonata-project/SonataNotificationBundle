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

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Interop\Amqp\AmqpContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\AMQPBackend;
use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Sonata\NotificationBundle\Exception\BackendNotFoundException;
use Sonata\NotificationBundle\Tests\Mock\AmqpConnectionFactoryStub;

class AMQPBackendDispatcherTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(AmqpConnectionFactory::class)) {
            static::markTestSkipped('enqueue/amqp-lib library is not installed');
        }

        AmqpConnectionFactoryStub::$config = null;
        AmqpConnectionFactoryStub::$context = null;
    }

    public function testThrowIfSettingsMissFactoryClassOptionOnGetContext(): void
    {
        $dispatcher = new AMQPBackendDispatcher([], [], 'default', []);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The factory_class option is missing though it is required.');
        $dispatcher->getContext();
    }

    public function testThrowIfFactoryClassIsNotRealClass(): void
    {
        $dispatcher = new AMQPBackendDispatcher(['factory_class' => 'anInvalidClass'], [], 'default', []);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The factory_class option "anInvalidClass" has to be valid class that implements "Interop\Amqp\AmqpConnectionFactory"');
        $dispatcher->getContext();
    }

    public function testThrowIfFactoryClassIsNotInstanceOfAmqpConnectionFactoryInterface(): void
    {
        $dispatcher = new AMQPBackendDispatcher(['factory_class' => \stdClass::class], [], 'default', []);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The factory_class option "stdClass" has to be valid class that implements "Interop\Amqp\AmqpConnectionFactory"');
        $dispatcher->getContext();
    }

    public function testShouldPassExpectedOptionsToAmqpConnectionFactoryConstructor(): void
    {
        $dispatcher = new AMQPBackendDispatcher(
            [
                'host' => 'theHost',
                'port' => 'thePort',
                'user' => 'theUser',
                'pass' => 'thePass',
                'vhost' => 'theVhost',
                'factory_class' => AmqpConnectionFactoryStub::class,
            ],
            [],
            'default',
            []
        );

        $dispatcher->getContext();

        static::assertSame([
            'host' => 'theHost',
            'port' => 'thePort',
            'user' => 'theUser',
            'pass' => 'thePass',
            'vhost' => 'theVhost',
        ], AmqpConnectionFactoryStub::$config);
    }

    public function testShouldReturnExpectedAmqpContext(): void
    {
        $expectedContext = $this->createMock(AmqpContext::class);

        $dispatcher = new AMQPBackendDispatcher(
            [
                'host' => 'aHost',
                'port' => 'aPort',
                'user' => 'aUser',
                'pass' => 'aPass',
                'vhost' => 'aVhost',
                'factory_class' => AmqpConnectionFactoryStub::class,
            ],
            [],
            'default',
            []
        );

        AmqpConnectionFactoryStub::$context = $expectedContext;

        $actualContext = $dispatcher->getContext();

        static::assertSame($expectedContext, $actualContext);
    }

    public function testQueue(): void
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', static::once());
        $mock2 = $this->getMockQueue('bar', 'message.type.foo', static::never());
        $fooBackend = ['type' => 'message.type.foo', 'backend' => $mock];
        $barBackend = ['type' => 'message.type.bar', 'backend' => $mock2];
        $backends = [$fooBackend, $barBackend];
        $dispatcher = $this->getDispatcher($backends);
        $dispatcher->createAndPublish('message.type.foo', []);
    }

    public function testDefaultQueue(): void
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', static::once());
        $fooBackend = ['type' => 'default', 'backend' => $mock];
        $dispatcher = $this->getDispatcher([$fooBackend]);
        $dispatcher->createAndPublish('some.other.type', []);
    }

    public function testDefaultQueueNotFound(): void
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', static::never());
        $fooBackend = ['type' => 'message.type.foo', 'backend' => $mock];
        $dispatcher = $this->getDispatcher([$fooBackend]);

        $this->expectException(BackendNotFoundException::class);
        $dispatcher->createAndPublish('some.other.type', []);
    }

    public function testInvalidQueue(): void
    {
        $mock = $this->getMockQueue('foo', 'message.type.bar');
        $dispatcher = $this->getDispatcher(
            [['type' => 'bar', 'backend' => $mock]],
            [['queue' => 'foo', 'routing_key' => 'message.type.bar']]
        );

        $this->expectException(BackendNotFoundException::class);
        $dispatcher->createAndPublish('message.type.bar', []);
    }

    public function testAllQueueInitializeOnce(): void
    {
        $queues = [
            ['queue' => 'foo', 'routing_key' => 'message.type.foo'],
            ['queue' => 'bar', 'routing_key' => 'message.type.bar'],
            ['queue' => 'baz', 'routing_key' => 'message.type.baz'],
        ];

        $backends = [];

        foreach ($queues as $queue) {
            $mock = $this->getMockQueue($queue['queue'], $queue['routing_key']);
            $mock->expects(static::once())
                ->method('initialize');
            $backends[] = ['type' => $queue['routing_key'], 'backend' => $mock];
        }

        $dispatcher = $this->getDispatcher($backends, $queues);

        $dispatcher->createAndPublish('message.type.foo', []);
        $dispatcher->createAndPublish('message.type.foo', []);
    }

    protected function getDispatcher(array $backends, array $queues = [['queue' => 'foo', 'routing_key' => 'message.type.foo']]): AMQPBackendDispatcher
    {
        $settings = [
                'host' => 'foo',
                'port' => 'port',
                'user' => 'user',
                'pass' => 'pass',
                'vhost' => '/',
        ];

        return new AMQPBackendDispatcher($settings, $queues, 'default', $backends);
    }

    private function getMockQueue($queue, $type, $called = null): MockObject
    {
        $methods = ['createAndPublish', 'initialize'];
        $args = ['', 'foo', false, 'message.type.foo'];
        $mock = $this->getMockBuilder(AMQPBackend::class)
                     ->setConstructorArgs($args)
                     ->setMethods($methods)
                     ->getMock();

        if (null !== $called) {
            $mock->expects($called)
                ->method('createAndPublish');
        }

        return $mock;
    }
}
