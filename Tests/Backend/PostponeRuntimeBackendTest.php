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

use Sonata\NotificationBundle\Backend\BackendStatus;
use Sonata\NotificationBundle\Backend\PostponeRuntimeBackend;
use Sonata\NotificationBundle\Consumer\ConsumerEventInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @covers Sonata\NotificationBundle\Backend\PostponeRuntimeBackend
 */
class PostponeRuntimeBackendTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratorContainsPublishedMessages()
    {
        $backend = new PostponeRuntimeBackend($this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'), 'fake_sapi');

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

    public function testLiveEnvironment()
    {
        $dispatcher = new EventDispatcher();
        $backend = new PostponeRuntimeBackend($dispatcher, 'fake_sapi');
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
        $backend = new PostponeRuntimeBackend($this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface'), 'fake_sapi');

        $status = $backend->getStatus();

        $this->assertInstanceOf('Sonata\NotificationBundle\Backend\BackendStatus', $status);
        $this->assertEquals(BackendStatus::OK, $status->getStatus());;
    }
}
