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
use PhpAmqpLib\Channel\AMQPChannel;
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
            $this->markTestSkipped('enqueue/amqp-lib library is not installed');
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
        $contextMock->expects($this->once())
            ->method('createQueue')
            ->with($this->identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue) {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame([], $queue->getArguments());
            });

        $contextMock->expects($this->once())
            ->method('createTopic')
            ->with($this->identicalTo(self::EXCHANGE))
            ->willReturn($topic);

        $contextMock->expects($this->once())
            ->method('declareTopic')
            ->with($this->identicalTo($topic))
            ->willReturnCallback(function (AmqpTopic $topic) {
                $this->assertTrue((bool) ($topic->getFlags() & AmqpTopic::FLAG_DURABLE));
                $this->assertSame(AmqpTopic::TYPE_DIRECT, $topic->getType());
                $this->assertSame([], $topic->getArguments());
            });

        $contextMock->expects($this->once())
            ->method('bind')
            ->with($this->isInstanceOf(AmqpBind::class))
            ->willReturnCallback(function (ImplAmqpBind $bind) use ($queue, $topic) {
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
        $contextMock->expects($this->once())
            ->method('createQueue')
            ->with($this->identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue) {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame(['x-dead-letter-exchange' => self::DEAD_LETTER_EXCHANGE], $queue->getArguments());
            });

        $contextMock->expects($this->exactly(2))
            ->method('createTopic')
            ->willReturnMap([
                [self::EXCHANGE, $topic],
                [self::DEAD_LETTER_EXCHANGE, $deadLetterTopic],
            ]);

        $contextMock->expects($this->atLeastOnce())
            ->method('bind')
            ->with($this->isInstanceOf(AmqpBind::class));

        $contextMock->expects($this->exactly(2))
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
        $contextMock->expects($this->once())
            ->method('createQueue')
            ->with($this->identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue) {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame(
                    [
                        'x-dead-letter-exchange' => self::DEAD_LETTER_EXCHANGE,
                        'x-dead-letter-routing-key' => self::DEAD_LETTER_ROUTING_KEY,
                    ],
                    $queue->getArguments()
                );
            });

        $contextMock->expects($this->once())
            ->method('createTopic')
            ->with($this->identicalTo(self::EXCHANGE))
            ->willReturn($topic);

        $contextMock->expects($this->once())
            ->method('declareTopic')
            ->with($this->identicalTo($topic));

        $contextMock->expects($this->once())
            ->method('bind')
            ->with($this->isInstanceOf(AmqpBind::class));

        AmqpConnectionFactoryStub::$context = $contextMock;

        $backend->initialize();
    }

    public function testInitializeWithTTL(): void
    {
        $backend = $this->buildBackend(false, null, null, self::TTL);

        $queue = new ImplAmqpQueue(self::QUEUE);
        $topic = new ImplAmqpTopic(self::EXCHANGE);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects($this->once())
            ->method('createQueue')
            ->with($this->identicalTo(self::QUEUE))
            ->willReturn($queue);

        $contextMock->expects($this->once())
            ->method('declareQueue')
            ->with($this->identicalTo($queue))
            ->willReturnCallback(function (AmqpQueue $queue) {
                $this->assertTrue((bool) ($queue->getFlags() & AmqpQueue::FLAG_DURABLE));
                $this->assertSame(['x-message-ttl' => self::TTL], $queue->getArguments());
            });

        $contextMock->expects($this->once())
            ->method('createTopic')
            ->with($this->identicalTo(self::EXCHANGE))
            ->willReturn($topic);

        $contextMock->expects($this->once())
            ->method('declareTopic')
            ->with($this->identicalTo($topic));

        $contextMock->expects($this->once())
            ->method('bind')
            ->with($this->isInstanceOf(AmqpBind::class));

        AmqpConnectionFactoryStub::$context = $contextMock;

        $backend->initialize();
    }

    public function testGetIteratorWithNoPrefetchCount(): void
    {
        $backend = $this->buildBackend();

        $queue = new ImplAmqpQueue('aQueue');

        $consumerMock = $this->createMock(AmqpConsumer::class);
        $consumerMock->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects($this->never())
            ->method('setQos');

        $contextMock->expects($this->once())
            ->method('createQueue')
            ->willReturn($queue);

        $contextMock->expects($this->once())
            ->method('createConsumer')
            ->with($this->identicalTo($queue))
            ->willReturn($consumerMock);

        $contextMock->expects($this->once())
            ->method('getLibChannel')
            ->willReturn($this->createMock(AMQPChannel::class));

        AmqpConnectionFactoryStub::$context = $contextMock;

        $iterator = $backend->getIterator();

        $this->assertInstanceOf(AMQPMessageIterator::class, $iterator);
    }

    public function testGetIteratorWithPrefetchCount(): void
    {
        $backend = $this->buildBackend(false, null, null, null, self::PREFETCH_COUNT);

        $queue = new ImplAmqpQueue('aQueue');
        $consumerMock = $this->createMock(AmqpConsumer::class);
        $consumerMock->expects($this->once())
            ->method('getQueue')
            ->willReturn($queue);

        $contextMock = $this->createMock(AmqpContext::class);
        $contextMock->expects($this->once())
            ->method('setQos')
            ->with($this->isNull(), $this->identicalTo(self::PREFETCH_COUNT), $this->isFalse());

        $contextMock->expects($this->once())
            ->method('createQueue')
            ->willReturn($queue);

        $contextMock->expects($this->once())
            ->method('createConsumer')
            ->with($this->identicalTo($queue))
            ->willReturn($consumerMock);

        $contextMock->expects($this->once())
            ->method('getLibChannel')
            ->willReturn($this->createMock(AMQPChannel::class));

        AmqpConnectionFactoryStub::$context = $contextMock;

        $iterator = $backend->getIterator();

        $this->assertInstanceOf(AMQPMessageIterator::class, $iterator);
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

        $dispatcherMock = $this->getMockBuilder(AMQPBackendDispatcher::class)
            ->setConstructorArgs([$settings, $queues, 'default', [['type' => self::KEY, 'backend' => $backend]]])
            ->getMock();

        $backend->setDispatcher($dispatcherMock);

        return $backend;
    }
}
