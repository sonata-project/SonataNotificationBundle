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

use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;
use Sonata\NotificationBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class AMQPBackendDispatcherTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('PhpAmqpLib\Message\AMQPMessage')) {
            $this->markTestSkipped('AMQP Lib not installed');
        }
    }

    public function testQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->once());
        $mock2 = $this->getMockQueue('bar', 'message.type.foo', $this->never());
        $fooBackend = array('type' => 'message.type.foo', 'backend' => $mock);
        $barBackend = array('type' => 'message.type.bar', 'backend' => $mock2);
        $backends = array($fooBackend, $barBackend);
        $dispatcher = $this->getDispatcher($backends);
        $dispatcher->createAndPublish('message.type.foo', array());
    }

    public function testDefaultQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->once());
        $fooBackend = array('type' => 'default', 'backend' => $mock);
        $dispatcher = $this->getDispatcher(array($fooBackend));
        $dispatcher->createAndPublish('some.other.type', array());
    }

    public function testDefaultQueueNotFound()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->never());
        $fooBackend = array('type' => 'message.type.foo', 'backend' => $mock);
        $dispatcher = $this->getDispatcher(array($fooBackend));

        $this->setExpectedException('\Sonata\NotificationBundle\Exception\BackendNotFoundException');
        $dispatcher->createAndPublish('some.other.type', array());
    }

    public function testInvalidQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.bar');
        $dispatcher = $this->getDispatcher(
            array(array('type' => 'bar', 'backend' => $mock)),
            array(array('queue' => 'foo', 'routing_key' => 'message.type.bar'))
        );

        $this->setExpectedException('\Sonata\NotificationBundle\Exception\BackendNotFoundException');
        $dispatcher->createAndPublish('message.type.bar', array());
    }

    public function testAllQueueInitializeOnce()
    {
        $queues = array(
            array('queue' => 'foo', 'routing_key' => 'message.type.foo'),
            array('queue' => 'bar', 'routing_key' => 'message.type.bar'),
            array('queue' => 'baz', 'routing_key' => 'message.type.baz'),
        );

        $backends = array();

        foreach ($queues as $queue) {
            $mock = $this->getMockQueue($queue['queue'], $queue['routing_key']);
            $mock->expects($this->once())
                ->method('initialize');
            $backends[] = array('type' => $queue['routing_key'], 'backend' => $mock);
        }

        $dispatcher = $this->getDispatcher($backends, $queues);

        $dispatcher->createAndPublish('message.type.foo', array());
        $dispatcher->createAndPublish('message.type.foo', array());
    }

    protected function getMockQueue($queue, $type, $called = null)
    {
        $methods = array('createAndPublish', 'initialize');
        $args = array('', 'foo', false, 'message.type.foo');
        $mock = $this->getMockBuilder('Sonata\NotificationBundle\Backend\AMQPBackend')
                     ->setConstructorArgs($args)
                     ->setMethods($methods)
                     ->getMock();

        if ($called !== null) {
            $mock->expects($called)
                ->method('createAndPublish')
            ;
        }

        return $mock;
    }

    protected function getDispatcher(array $backends, array $queues = array(array('queue' => 'foo', 'routing_key' => 'message.type.foo')))
    {
        $settings = array(
                'host' => 'foo',
                'port' => 'port',
                'user' => 'user',
                'pass' => 'pass',
                'vhost' => '/',
        );

        return new AMQPBackendDispatcher($settings, $queues, 'default', $backends);
    }
}
