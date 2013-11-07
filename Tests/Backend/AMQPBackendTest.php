<?php
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
        $methods = array('createAndPublish');
        $args = array(array(), '', 'foo', 'message.type.foo');
        $mock = $this->getMock('Sonata\NotificationBundle\Backend\AMQPBackend', $methods, $args);

        if ($called !== null) {
            $mock->expects($called)
                ->method('createAndPublish')
            ;
        }

        return $mock;
    }

    protected function getDispatcher(array $backends, $queue = 'foo', $key = 'message.type.foo')
    {
        $queues = array(array('queue' => $queue, 'routing_key' => $key));
        $settings = array(
                'host' => 'foo',
                'port' => 'port',
                'user' => 'user',
                'pass' => 'pass',
                'vhost' => '/'
        );

        return new AMQPBackendDispatcher($settings, $queues, 'default', $backends);
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

    /**
     * @expectedException \RuntimeException
     */
    public function testDefaultQueueNotFound()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->never());
        $fooBackend = array('type' => 'message.type.foo', 'backend' => $mock);
        $dispatcher = $this->getDispatcher(array($fooBackend));
        $dispatcher->createAndPublish('some.other.type', array());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.bar');
        $dispatcher = $this->getDispatcher(array(array('type' => 'bar', 'backend' => $mock)), 'foo', 'message.type.bar');
        $dispatcher->createAndPublish('message.type.bar', array());
    }
}
