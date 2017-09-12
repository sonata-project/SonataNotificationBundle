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

use Sonata\NotificationBundle\Backend\AMQPBackend;
use Sonata\NotificationBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class AMQPBackendTest extends PHPUnit_Framework_TestCase
{
    const EXCHANGE = 'exchange';
    const QUEUE = 'foo';
    const KEY = 'message.type.foo';
    const DEAD_LETTER_EXCHANGE = 'dlx';
    const DEAD_LETTER_ROUTING_KEY = 'message.type.dl';
    const TTL = 60000;
    const PREFETCH_COUNT = 1;

    protected function setUp()
    {
        if (!class_exists('PhpAmqpLib\Channel\AMQPChannel')) {
            $this->markTestSkipped('AMQP Lib not installed');
        }
    }

    public function testInitializeWithNoDeadLetterExchangeAndNoDeadLetterRoutingKey()
    {
        list($backend, $channelMock) = $this->getBackendAndChannelMock();

        $channelMock->expects($this->once())
            ->method('exchange_declare')
            ->with(
                $this->equalTo(self::EXCHANGE),
                $this->equalTo('direct'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean')
            );
        $channelMock->expects($this->once())
            ->method('queue_declare')
            ->with(
                $this->equalTo(self::QUEUE),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->equalTo(array())
            );
        $channelMock->expects($this->once())
            ->method('queue_bind')
            ->with(
                $this->equalTo(self::QUEUE),
                $this->equalTo(self::EXCHANGE),
                $this->equalTo(self::KEY)
            );

        $backend->initialize();
    }

    public function testInitializeWithDeadLetterExchangeAndNoDeadLetterRoutingKey()
    {
        list($backend, $channelMock) = $this->getBackendAndChannelMock(false, self::DEAD_LETTER_EXCHANGE);

        $channelMock->expects($this->exactly(2))
            ->method('exchange_declare')
            ->withConsecutive(
                array(
                    $this->equalTo(self::EXCHANGE),
                    $this->equalTo('direct'),
                    $this->isType('boolean'),
                    $this->isType('boolean'),
                    $this->isType('boolean'),
                ),
                array(
                    $this->equalTo(self::DEAD_LETTER_EXCHANGE),
                    $this->equalTo('direct'),
                    $this->isType('boolean'),
                    $this->isType('boolean'),
                    $this->isType('boolean'),
                )
            );
        $channelMock->expects($this->once())
            ->method('queue_declare')
            ->with(
                $this->equalTo(self::QUEUE),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->equalTo(array(
                    'x-dead-letter-exchange' => array('S', self::DEAD_LETTER_EXCHANGE),
                ))
            );
        $channelMock->expects($this->exactly(2))
            ->method('queue_bind')
            ->withConsecutive(
                array(
                   $this->equalTo(self::QUEUE),
                   $this->equalTo(self::EXCHANGE),
                   $this->equalTo(self::KEY),
                ),
                array(
                   $this->equalTo(self::QUEUE),
                   $this->equalTo(self::DEAD_LETTER_EXCHANGE),
                   $this->equalTo(self::KEY),
                )
            );

        $backend->initialize();
    }

    public function testInitializeWithDeadLetterExchangeAndDeadLetterRoutingKey()
    {
        list($backend, $channelMock) = $this->getBackendAndChannelMock(false, self::DEAD_LETTER_EXCHANGE, self::DEAD_LETTER_ROUTING_KEY);

        $channelMock->expects($this->once())
            ->method('exchange_declare')
            ->with(
                $this->equalTo(self::EXCHANGE),
                $this->equalTo('direct'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean')
            );
        $channelMock->expects($this->once())
            ->method('queue_declare')
            ->with(
                $this->equalTo(self::QUEUE),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->equalTo(array(
                   'x-dead-letter-exchange' => array('S', self::DEAD_LETTER_EXCHANGE),
                   'x-dead-letter-routing-key' => array('S', self::DEAD_LETTER_ROUTING_KEY),
                ))
            );
        $channelMock->expects($this->once())
            ->method('queue_bind')
            ->with(
                $this->equalTo(self::QUEUE),
                $this->equalTo(self::EXCHANGE),
                $this->equalTo(self::KEY)
            );

        $backend->initialize();
    }

    public function testInitializeWithTTL()
    {
        list($backend, $channelMock) = $this->getBackendAndChannelMock(false, null, null, self::TTL);

        $channelMock->expects($this->once())
            ->method('exchange_declare')
            ->with(
                $this->equalTo(self::EXCHANGE),
                $this->equalTo('direct'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean')
            );
        $channelMock->expects($this->once())
            ->method('queue_declare')
            ->with(
                $this->equalTo(self::QUEUE),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->isType('boolean'),
                $this->equalTo(array(
                    'x-message-ttl' => array('I', self::TTL),
                ))
            );
        $channelMock->expects($this->once())
            ->method('queue_bind')
            ->with(
                $this->equalTo(self::QUEUE),
                $this->equalTo(self::EXCHANGE),
                $this->equalTo(self::KEY)
            );

        $backend->initialize();
    }

    public function testGetIteratorWithNoPrefetchCount()
    {
        list($backend, $channelMock) = $this->getBackendAndChannelMock();

        $channelMock->expects($this->never())
            ->method('basic_qos');

        $backend->getIterator();
    }

    public function testGetIteratorWithPrefetchCount()
    {
        list($backend, $channelMock) = $this->getBackendAndChannelMock(false, null, null, null, self::PREFETCH_COUNT);

        $channelMock->expects($this->once())
            ->method('basic_qos')
            ->with(
                $this->isNull(),
                $this->equalTo(self::PREFETCH_COUNT),
                $this->isNull()
            );

        $backend->getIterator();
    }

    protected function getBackendAndChannelMock($recover = false, $deadLetterExchange = null, $deadLetterRoutingKey = null, $ttl = null, $prefetchCount = null)
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

        $settings = array(
            'host' => 'foo',
            'port' => 'port',
            'user' => 'user',
            'pass' => 'pass',
            'vhost' => '/',
        );

        $queues = array(
            array('queue' => self::QUEUE, 'routing_key' => self::KEY),
        );

        $channelMock = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->disableOriginalConstructor()
            ->setMethods(array('queue_declare', 'exchange_declare', 'queue_bind', 'basic_qos'))
            ->getMock();

        $dispatcherMock = $this->getMockBuilder('\Sonata\NotificationBundle\Backend\AMQPBackendDispatcher')
            ->setConstructorArgs(array($settings, $queues, 'default', array(array('type' => self::KEY, 'backend' => $backend))))
            ->setMethods(array('getChannel'))
            ->getMock();

        $dispatcherMock->method('getChannel')
            ->willReturn($channelMock);

        $backend->setDispatcher($dispatcherMock);

        return array($backend, $channelMock);
    }
}
