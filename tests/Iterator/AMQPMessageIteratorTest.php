<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Iterator;

use Interop\Amqp\AmqpConsumer;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\Impl\AmqpMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Iterator\AMQPMessageIterator;
use Sonata\NotificationBundle\Iterator\MessageIteratorInterface;
use Sonata\NotificationBundle\Model\Message;

/**
 * @covers \Sonata\NotificationBundle\Iterator\AMQPMessageIterator
 */
class AMQPMessageIteratorTest extends TestCase
{
    public function testShouldImplementMessageIteratorInterface()
    {
        $rc = new \ReflectionClass(AMQPMessageIterator::class);

        $this->assertTrue($rc->implementsInterface(MessageIteratorInterface::class));
    }

    public function testCouldBeConstructedWithChannelAndContextAsArguments()
    {
        new AMQPMessageIterator($this->createChannelMock(), $this->createConsumerStub());
    }

    public function testShouldIterateOverThreeMessagesAndExit()
    {
        $firstMessage = new AmqpMessage('{"body": {"value": "theFirstMessageBody"}, "type": "aType", "state": "aState"}');
        $secondMessage = new AmqpMessage('{"body": {"value": "theSecondMessageBody"}, "type": "aType", "state": "aState"}');
        $thirdMessage = new AmqpMessage('{"body": {"value": "theThirdMessageBody"}, "type": "aType", "state": "aState"}');

        $consumerMock = $this->createConsumerStub('aQueueName');
        $consumerMock
            ->expects($this->exactly(4))
            ->method('receive')
            ->willReturnOnConsecutiveCalls($firstMessage, $secondMessage, $thirdMessage, null);

        $iterator = new AMQPMessageIterator($this->createChannelMock(), $consumerMock);

        $values = [];
        foreach ($iterator as $message) {
            /* @var Message $message */

            $this->assertInstanceOf(Message::class, $message);
            $this->assertInstanceOf(\Interop\Amqp\AmqpMessage::class, $message->getValue('interopMessage'));
            $this->assertInstanceOf(\PhpAmqpLib\Message\AMQPMessage::class, $message->getValue('AMQMessage'));

            $values[] = $message->getValue('value');
        }

        $this->assertEquals(['theFirstMessageBody', 'theSecondMessageBody', 'theThirdMessageBody'], $values);
    }

    /**
     * @param mixed $queueName
     *
     * @return AmqpConsumer|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createConsumerStub($queueName = null)
    {
        $queue = $this->createMock(AmqpQueue::class);
        $queue
            ->expects($this->any())
            ->method('getQueueName')
            ->willReturn($queueName)
        ;

        $consumer = $this->createMock(AmqpConsumer::class);
        $consumer
            ->expects($this->any())
            ->method('getQueue')
            ->willReturn($queue)
        ;

        return $consumer;
    }

    /**
     * @return AMQPChannel|\PHPUnit_Framework_MockObject_MockObject|AMQPChannel
     */
    private function createChannelMock()
    {
        return $this->createMock(AMQPChannel::class);
    }
}
