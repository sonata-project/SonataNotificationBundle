<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Backend;

use Sonata\NotificationBundle\Backend\PostponeRuntimeBackend;
use Sonata\NotificationBundle\Consumer\ConsumerEventInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Liip\Monitor\Result\CheckResult;

/**
 * @covers Sonata\NotificationBundle\Backend\PostponeRuntimeBackend
 */

class PostponeRuntimeBackendTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorContainsPublishedMessages()
    {
        $backend = new PostponeRuntimeBackend($this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'), true);

        $messages = array();

        $message = $backend->create('foo', array());
        $messages[] = $message;
        $backend->publish($message);

        $message = $backend->create('bar', array());
        $messages[] = $message;
        $backend->publish($message);

        $message = $backend->create('baz', array());
        $messages[] = $message;
        $backend->publish($message);

        $backend->create('not_published', array());

        $iterator = $backend->getIterator();
        foreach ($iterator as $eachKey => $eachMessage) {
            $this->assertSame($messages[$eachKey], $eachMessage);
        }
    }

    public function testNoMessagesOnEvent()
    {
        $backend = $this->getMock('Sonata\NotificationBundle\Backend\PostponeRuntimeBackend', array('handle'), array($this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'), true));
        $backend
            ->expects($this->never())
            ->method('handle')
        ;

        $backend->onEvent();
    }

    public function testLiveEnvironment()
    {
        $dispatcher = new EventDispatcher();
        $backend = new PostponeRuntimeBackend($dispatcher, true);
        $dispatcher->addListener('kernel.terminate', array($backend, 'onEvent'));

        $message = $backend->create('notification.demo', array());
        $backend->publish($message);

        // This message will not be handled.
        $backend->create('notification.demo', array());

        $phpunit = $this;
        $phpunit->passed = false;
        $dispatcher->addListener('notification.demo', function (ConsumerEventInterface $event) use ($phpunit, $message) {
            $phpunit->assertSame($message, $event->getMessage());

            $phpunit->passed = true;
        });

        $dispatcher->dispatch('kernel.terminate');

        $this->assertTrue($phpunit->passed);
        $this->assertEquals(MessageInterface::STATE_DONE, $message->getState());
    }

    public function testStatusIsOk()
    {
        $backend = new PostponeRuntimeBackend($this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'), true);

        $status = $backend->getStatus();
        $this->assertInstanceOf('Liip\Monitor\Result\CheckResult', $status);
        $this->assertEquals(CheckResult::OK, $status->getStatus());;
    }

    public function testOnCliPublishHandlesDirectly()
    {
        $backend = $this->getMock('Sonata\NotificationBundle\Backend\PostponeRuntimeBackend', array('handle'), array($this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'), false));
        $backend
            ->expects($this->once())
            ->method('handle')
        ;

        $message = $backend->create('notification.demo', array());
        $backend->publish($message);
    }
}
