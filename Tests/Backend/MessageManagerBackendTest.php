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

use Sonata\NotificationBundle\Backend\MessageManagerBackend;
use Sonata\NotificationBundle\Entity\Message;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Exception\HandlingException;

class MessageManagerProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAndPublish()
    {
        $message = new Message();
        $modelManager = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $modelManager->expects($this->once())->method('save')->will($this->returnValue($message));
        $modelManager->expects($this->once())->method('create')->will($this->returnValue($message));

        $backend = new MessageManagerBackend($modelManager);
        $message = $backend->createAndPublish('foo', array('message' => 'salut'));

        $this->assertInstanceOf("Sonata\\NotificationBundle\\Model\\MessageInterface", $message);
        $this->assertEquals(MessageInterface::STATE_OPEN, $message->getState());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertEquals('foo', $message->getType());
        $this->assertEquals(array('message' => 'salut'), $message->getBody());
    }

    public function testHandleSuccess()
    {
        $message = new Message();
        $modelManager = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $modelManager->expects($this->exactly(2))->method('save')->will($this->returnValue($message));

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher->expects($this->once())->method('dispatch');

        $backend = new MessageManagerBackend($modelManager);

        $backend->handle($message, $dispatcher);

        $this->assertEquals(MessageInterface::STATE_DONE, $message->getState());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertNotNull($message->getCompletedAt());
    }

    public function testHandleError()
    {
        $message = new Message();
        $modelManager = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $modelManager->expects($this->exactly(2))->method('save')->will($this->returnValue($message));

        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher->expects($this->once())->method('dispatch')->will($this->throwException(new \RuntimeException));
        $backend = new MessageManagerBackend($modelManager);

        $e = false;
        try {
            $backend->handle($message, $dispatcher);
        } catch (HandlingException $e) {

        }

        $this->assertInstanceOf('Sonata\NotificationBundle\Exception\HandlingException', $e);

        $this->assertEquals(MessageInterface::STATE_ERROR, $message->getState());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertNotNull($message->getCompletedAt());
    }
}