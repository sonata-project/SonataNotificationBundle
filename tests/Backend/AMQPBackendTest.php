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
use Enqueue\AmqpLib\AmqpConsumer;
use Enqueue\AmqpLib\AmqpContext;
use Interop\Amqp\AmqpBind;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind as ImplAmqpBind;
use Interop\Amqp\Impl\AmqpQueue as ImplAmqpQueue;
use Interop\Amqp\Impl\AmqpTopic as ImplAmqpTopic;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\AMQPBackend;
use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Sonata\NotificationBundle\Iterator\AMQPMessageIterator;
use Sonata\NotificationBundle\Tests\Mock\AmqpConnectionFactoryStub;

class AMQPBackendTest extends TestCase
{
    public const EXCHANGE = 'exchange';
    public const QUEUE = 'foo';
    public const KEY = 'message.type.foo';
    public const DEAD_LETTER_EXCHANGE = 'dlx';
    public const DEAD_LETTER_ROUTING_KEY = 'message.type.dl';
    public const TTL = 60000;
    public const PREFETCH_COUNT = 1;

    protected function setUp(): void
    {
        if (!class_exists(AmqpConnectionFactory::class)) {
            static::markTestSkipped('enqueue/amqp-lib library is not installed');
        }

        AmqpConnectionFactoryStub::$context = null;
        AmqpConnectionFactoryStub::$config = null;
    }

    public function testInitializeWithNoDeadLetterExchangeAndNoDeadLetterRoutingKey(): void
    {
        $backend = $this->buildBackend();

        $queue = new ImplAmqpQueue(self::QUEUE);
        $topic = new ImplAmqpTopic(self::EXCHANGE);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects(static::once())
            ->method('createQueue')
            ->with(static::identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects(static::once())
            ->method('declareQueue')
            ->with(static::identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue): void {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame([], $queue->getArguments());
            });

        $contextMock->expects(static::once())
            ->method('createTopic')
            ->with(static::identicalTo(self::EXCHANGE))
            ->willReturn($topic);

        $contextMock->expects(static::once())
            ->method('declareTopic')
            ->with(static::identicalTo($topic))
            ->willReturnCallback(function (AmqpTopic $topic): void {
                $this->assertTrue((bool) ($topic->getFlags() & AmqpTopic::FLAG_DURABLE));
                $this->assertSame(AmqpTopic::TYPE_DIRECT, $topic->getType());
                $this->assertSame([], $topic->getArguments());
            });

        $contextMock->expects(static::once())
            ->method('bind')
            ->with(static::isInstanceOf(AmqpBind::class))
            ->willReturnCallback(function (ImplAmqpBind $bind) use ($queue, $topic): void {
                $this->assertSame($queue, $bind->getTarget());
                $this->assertSame($topic, $bind->getSource());
                $this->assertSame(self::KEY, $bind->getRoutingKey());
            });

        AmqpConnectionFactoryStub::$context = $contextMock;

        $backend->initialize();
    }

    public function testInitializeWithDeadLetterExchangeAndNoDeadLetterRoutingKey(): void
    {
        $backend = $this->buildBackend(false, self::DEAD_LETTER_EXCHANGE);

        $queue = new ImplAmqpQueue(self::QUEUE);
        $topic = new ImplAmqpTopic(self::EXCHANGE);
        $deadLetterTopic = new ImplAmqpTopic(self::DEAD_LETTER_EXCHANGE);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects(static::once())
            ->method('createQueue')
            ->with(static::identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects(static::once())
            ->method('declareQueue')
            ->with(static::identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue): void {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame(['x-dead-letter-exchange' => self::DEAD_LETTER_EXCHANGE], $queue->getArguments());
            });

        $contextMock->expects(static::exactly(2))
            ->method('createTopic')
            ->willReturnMap([
                [self::EXCHANGE, $topic],
                [self::DEAD_LETTER_EXCHANGE, $deadLetterTopic],
            ]);

        $contextMock->expects(static::atLeastOnce())
            ->method('bind')
            ->with(static::isInstanceOf(AmqpBind::class));

        $contextMock->expects(static::exactly(2))
            ->method('declareTopic')
            ->willReturnCallback(function (AmqpTopic $topic) use ($deadLetterTopic) {
                if ($topic === $deadLetterTopic) {
                    $this->assertTrue((bool) ($topic->getFlags() & AmqpTopic::FLAG_DURABLE));
                    $this->assertSame(AmqpTopic::TYPE_DIRECT, $topic->getType());
                    $this->assertSame([], $topic->getArguments());
                }
            });

        AmqpConnectionFactoryStub::$context = $contextMock;

        $backend->initialize();
    }

    public function testInitializeWithDeadLetterExchangeAndDeadLetterRoutingKey(): void
    {
        $backend = $this->buildBackend(false, self::DEAD_LETTER_EXCHANGE, self::DEAD_LETTER_ROUTING_KEY);

        $queue = new ImplAmqpQueue(self::QUEUE);
        $topic = new ImplAmqpTopic(self::EXCHANGE);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects(static::once())
            ->method('createQueue')
            ->with(static::identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects(static::once())
            ->method('declareQueue')
            ->with(static::identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue): void {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame(
                    [
                        'x-dead-letter-exchange' => self::DEAD_LETTER_EXCHANGE,
                        'x-dead-letter-routing-key' => self::DEAD_LETTER_ROUTING_KEY,
                    ],
                    $queue->getArguments()
                );
            });

        $contextMock->expects(static::once())
            ->method('createTopic')
            ->with(static::identicalTo(self::EXCHANGE))
            ->willReturn($topic);

        $contextMock->expects(static::once())
            ->method('declareTopic')
            ->with(static::identicalTo($topic));

        $contextMock->expects(static::once())
            ->method('bind')
            ->with(static::isInstanceOf(AmqpBind::class));

        AmqpConnectionFactoryStub::$context = $contextMock;

        $backend->initialize();
    }

    public function testInitializeWithTTL(): void
    {
        $backend = $this->buildBackend(false, null, null, self::TTL);

        $queue = new ImplAmqpQueue(self::QUEUE);
        $topic = new ImplAmqpTopic(self::EXCHANGE);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects(static::once())
            ->method('createQueue')
            ->with(static::identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects(static::once())
            ->method('declareQueue')
            ->with(static::identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue): void {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame(['x-message-ttl' => self::TTL], $queue->getArguments());
            });

        $contextMock->expects(static::once())
            ->method('createTopic')
            ->with(static::identicalTo(self::EXCHANGE))
            ->willReturn($topic);

        $contextMock->expects(static::once())
            ->method('declareTopic')
            ->with(static::identicalTo($topic));

        $contextMock->expects(static::once())
            ->method('bind')
            ->with(static::isInstanceOf(AmqpBind::class));

        AmqpConnectionFactoryStub::$context = $contextMock;

        $backend->initialize();
    }

    public function testGetIteratorWithNoPrefetchCount(): void
    {
        $backend = $this->buildBackend();

        $queue = new ImplAmqpQueue('aQueue');

        $consumerMock = $this->createMock(AmqpConsumer::class);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects(static::never())
            ->method('setQos');

        $contextMock->expects(static::once())
            ->method('createQueue')
            ->willReturn($queue);

        $contextMock->expects(static::once())
            ->method('createConsumer')
            ->with(static::identicalTo($queue))
            ->willReturn($consumerMock);

        AmqpConnectionFactoryStub::$context = $contextMock;

        $iterator = $backend->getIterator();

        static::assertInstanceOf(AMQPMessageIterator::class, $iterator);
    }

    public function testGetIteratorWithPrefetchCount(): void
    {
        $backend = $this->buildBackend(false, null, null, null, self::PREFETCH_COUNT);

        $queue = new ImplAmqpQueue('aQueue');
        $consumerMock = $this->createMock(AmqpConsumer::class);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects(static::once())
            ->method('setQos')
            ->with(static::isNull(), static::identicalTo(self::PREFETCH_COUNT), static::isFalse());

        $contextMock->expects(static::once())
            ->method('createQueue')
            ->willReturn($queue);

        $contextMock->expects(static::once())
            ->method('createConsumer')
            ->with(static::identicalTo($queue))
            ->willReturn($consumerMock);

        AmqpConnectionFactoryStub::$context = $contextMock;

        $iterator = $backend->getIterator();

        static::assertInstanceOf(AMQPMessageIterator::class, $iterator);
    }

    protected function buildBackend($recover = false, $deadLetterExchange = null, $deadLetterRoutingKey = null, $ttl = null, $prefetchCount = null): AMQPBackend
    {
        $backend = new AMQPBackend(
            self::EXCHANGE,
            self::QUEUE,
            $recover,
            self::KEY,
            $deadLetterExchange,
            $deadLetterRoutingKey,
            $ttl,
            $prefetchCount
        );

        $settings = [
            'host' => 'foo',
            'port' => 'port',
            'user' => 'user',
            'pass' => 'pass',
            'vhost' => '/',
            'factory_class' => AmqpConnectionFactoryStub::class,
        ];

        $queues = [
            ['queue' => self::QUEUE, 'routing_key' => self::KEY],
        ];

        $dispatcherMock = new AMQPBackendDispatcher(
            $settings,
            $queues,
            'default',
            [['type' => self::KEY, 'backend' => $backend]]
        );

        $backend->setDispatcher($dispatcherMock);

        return $backend;
    }
}
