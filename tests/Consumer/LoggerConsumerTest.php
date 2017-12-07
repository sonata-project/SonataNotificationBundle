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

namespace Sonata\NotificationBundle\Tests\Consumer;

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\LoggerConsumer;
use Sonata\NotificationBundle\Tests\Entity\Message;

class LoggerConsumerTest extends TestCase
{
    /**
     * @dataProvider calledTypeProvider
     *
     * @param $type
     * @param $calledType
     */
    public function testProcess($type, $calledType): void
    {
        $logger = $this->createMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method($calledType);

        $message = new Message();
        $message->setBody([
            'level' => $type,
            'message' => 'Alert - Area 52 get compromised!!',
        ]);

        $event = new ConsumerEvent($message);

        $consumer = new LoggerConsumer($logger);
        $consumer->process($event);
    }

    /**
     * @return array[]
     */
    public function calledTypeProvider()
    {
        return [
            ['emerg', 'emergency'],
            ['alert', 'alert'],
            ['crit', 'critical'],
            ['err', 'error'],
            ['warn', 'warning'],
            ['notice', 'notice'],
            ['info', 'info'],
            ['debug', 'debug'],
        ];
    }

    public function testInvalidType(): void
    {
        $this->expectException(\Sonata\NotificationBundle\Exception\InvalidParameterException::class);

        $logger = $this->createMock('Psr\Log\LoggerInterface');

        $message = new Message();
        $message->setBody([
            'level' => 'ERROR',
            'message' => 'Alert - Area 52 get compromised!!',
        ]);

        $event = new ConsumerEvent($message);

        $consumer = new LoggerConsumer($logger);
        $consumer->process($event);
    }
}
