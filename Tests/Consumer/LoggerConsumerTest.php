<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Consumer;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\LoggerConsumer;
use Sonata\NotificationBundle\Tests\Entity\Message;

class LoggerConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider calledTypeProvider
     *
     * @param $type
     * @param $calledType
     */
    public function testProcess($type, $calledType)
    {
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $logger->expects($this->once())->method($calledType);

        $message = new Message();
        $message->setBody(array(
            'level' => $type,
            'message' => 'Alert - Area 52 get compromised!!',
        ));

        $event = new ConsumerEvent($message);

        $consumer = new LoggerConsumer($logger);
        $consumer->process($event);
    }

    /**
     * @return array[]
     */
    public function calledTypeProvider()
    {
        return array(
            array('emerg', 'emergency'),
            array('alert', 'alert'),
            array('crit', 'critical'),
            array('err', 'error'),
            array('warn', 'warning'),
            array('notice', 'notice'),
            array('info', 'info'),
            array('debug', 'debug'),
        );
    }

    /**
     * @expectedException \Sonata\NotificationBundle\Exception\InvalidParameterException
     */
    public function testInvalidType()
    {
        $logger = $this->getMock('Psr\Log\LoggerInterface');

        $message = new Message();
        $message->setBody(array(
            'level' => 'ERROR',
            'message' => 'Alert - Area 52 get compromised!!',
        ));

        $event = new ConsumerEvent($message);

        $consumer = new LoggerConsumer($logger);
        $consumer->process($event);
    }
}
