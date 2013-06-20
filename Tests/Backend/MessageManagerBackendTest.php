<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Notification;

use Sonata\NotificationBundle\Backend\MessageManagerBackend;
use \Sonata\NotificationBundle\Tests\Entity\Message;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Exception\HandlingException;

use Liip\Monitor\Result\CheckResult;

class MessageManagerProducerTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAndPublish()
    {
        $message = new Message();
        $modelManager = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $modelManager->expects($this->once())->method('save')->will($this->returnValue($message));
        $modelManager->expects($this->once())->method('create')->will($this->returnValue($message));

        $backend = new MessageManagerBackend($modelManager, array());
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

        $backend = new MessageManagerBackend($modelManager, array());

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
        $backend = new MessageManagerBackend($modelManager, array());

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

    /**
     * @dataProvider statusProvider
     */
    public function testStatus($counts, $expectedStatus, $message)
    {
        $modelManager = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');
        $modelManager->expects($this->exactly(1))->method('countStates')->will($this->returnValue($counts));

        $backend = new MessageManagerBackend($modelManager, array(
            MessageInterface::STATE_IN_PROGRESS => 10,
            MessageInterface::STATE_ERROR => 30,
            MessageInterface::STATE_OPEN => 100,
            MessageInterface::STATE_DONE => 10000,
        ));

        $status = $backend->getStatus();

        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $status);
        $this->assertEquals($expectedStatus, $status->getStatus());
        $this->assertEquals($message, $status->getMessage());
    }

    public static function statusProvider()
    {
        $data = array();

        $data[] = array(
            array(
                MessageInterface::STATE_IN_PROGRESS => 11, #here
                MessageInterface::STATE_ERROR => 31,
                MessageInterface::STATE_OPEN => 100,
                MessageInterface::STATE_DONE => 10000,
            ),
            CheckResult::CRITICAL,
            'Too many messages processed at the same time (Database)'
        );

        $data[] = array(
            array(
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 31, #here
                MessageInterface::STATE_OPEN => 100,
                MessageInterface::STATE_DONE => 10000,
            ),
            CheckResult::CRITICAL,
            'Too many errors (Database)'
        );

        $data[] = array(
            array(
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 1,
                MessageInterface::STATE_OPEN => 101, #here
                MessageInterface::STATE_DONE => 10000,
            ),
            CheckResult::WARNING,
            'Too many messages waiting to be processed (Database)'
        );

        $data[] = array(
            array(
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 1,
                MessageInterface::STATE_OPEN => 100,
                MessageInterface::STATE_DONE => 10001, #here
            ),
            CheckResult::WARNING,
            'Too many processed messages, please clean the database (Database)'
        );

        $data[] = array(
            array(
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 1,
                MessageInterface::STATE_OPEN => 1,
                MessageInterface::STATE_DONE => 1,
            ),
            CheckResult::OK,
            'Ok (Database)'
        );

        return $data;
    }
}
