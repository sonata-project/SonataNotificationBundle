<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Page;

use Sonata\NotificationBundle\Producer\MessageManagerProducer;
use Sonata\NotificationBundle\Entity\Message;
use Sonata\NotificationBundle\Model\MessageInterface;

class MessageManagerProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAndPublish()
    {
        $message = new Message();
        $modelManager = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $modelManager->expects($this->once())->method('save')->will($this->returnValue($message));
        $modelManager->expects($this->once())->method('create')->will($this->returnValue($message));

        $producer = new MessageManagerProducer($modelManager);
        $message = $producer->createAndPublish('foo', array('message' => 'salut'));

        $this->assertInstanceOf("Sonata\\NotificationBundle\\Model\\MessageInterface", $message);
        $this->assertEquals(MessageInterface::STATE_OPEN, $message->getState());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertEquals('foo', $message->getType());
        $this->assertEquals(array('message' => 'salut'), $message->getBody());
    }
}