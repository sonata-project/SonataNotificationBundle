<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Notification;

use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;

class AMQPBackendTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('PhpAmqpLib\Message\AMQPMessage')) {
            $this->markTestSkipped('AMQP Lib not installed');
        }
    }

    protected function getMockQueue($queue, $type, $called = null)
    {
        $methods = ['createAndPublish'];
        $args = [[], '', 'foo', 'message.type.foo'];
        $mock = $this->getMock('Sonata\NotificationBundle\Backend\AMQPBackend', $methods, $args);

        if ($called !== null) {
            $mock->expects($called)
                ->method('createAndPublish');
        }

        return $mock;
    }

    protected function getDispatcher(array $backends, $queue = 'foo', $key = 'message.type.foo')
    {
        $queues = [['queue' => $queue, 'routing_key' => $key]];
        $settings = [
                'host'  => 'foo',
                'port'  => 'port',
                'user'  => 'user',
                'pass'  => 'pass',
                'vhost' => '/',
        ];

        return new AMQPBackendDispatcher($settings, $queues, 'default', $backends);
    }

    public function testQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->once());
        $mock2 = $this->getMockQueue('bar', 'message.type.foo', $this->never());
        $fooBackend = ['type' => 'message.type.foo', 'backend' => $mock];
        $barBackend = ['type' => 'message.type.bar', 'backend' => $mock2];
        $backends = [$fooBackend, $barBackend];
        $dispatcher = $this->getDispatcher($backends);
        $dispatcher->createAndPublish('message.type.foo', []);
    }

    public function testDefaultQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->once());
        $fooBackend = ['type' => 'default', 'backend' => $mock];
        $dispatcher = $this->getDispatcher([$fooBackend]);
        $dispatcher->createAndPublish('some.other.type', []);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDefaultQueueNotFound()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->never());
        $fooBackend = ['type' => 'message.type.foo', 'backend' => $mock];
        $dispatcher = $this->getDispatcher([$fooBackend]);
        $dispatcher->createAndPublish('some.other.type', []);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.bar');
        $dispatcher = $this->getDispatcher([['type' => 'bar', 'backend' => $mock]], 'foo', 'message.type.bar');
        $dispatcher->createAndPublish('message.type.bar', []);
    }
}
