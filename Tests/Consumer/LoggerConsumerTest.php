<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Consumer;

use Sonata\NotificationBundle\Consumer\LoggerConsumer;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Tests\Entity\Message;

class LoggerConsumerTest extends \PHPUnit_Framework_TestCase
{

    public function testProcess()
    {
        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');
        $logger->expects($this->once())->method('crit');

        $message = new Message();
        $message->setBody(array(
            'level'   => 'crit',
            'message' => 'Alert - Area 52 get compromised!!'
        ));

        $event = new ConsumerEvent($message);

        $consumer = new LoggerConsumer($logger);
        $consumer->process($event);
    }

    /**
     * @expectedException \Sonata\NotificationBundle\Exception\InvalidParameterException
     */
    public function testInvalidType()
    {
        $logger = $this->getMock('Symfony\Component\HttpKernel\Log\LoggerInterface');

        $message = new Message();
        $message->setBody(array(
            'level'   => 'ERROR',
            'message' => 'Alert - Area 52 get compromised!!'
        ));

        $event = new ConsumerEvent($message);

        $consumer = new LoggerConsumer($logger);
        $consumer->process($event);
    }
}
