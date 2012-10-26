<?php
namespace Sonata\NotificationBundle\Tests\Notification;

use Sonata\NotificationBundle\Backend\AMQPBackendDispatcher;

class AMQPBackendTest extends \PHPUnit_Framework_TestCase
{
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
    
    protected function getDispatcher($queue = 'foo', $key = 'message.type.foo')
    {
        $queues = array(array('queue' => $queue, 'routing_key' => $key));
        
        $settings = array(
                'host' => 'foo',
                'port' => 'port',
                'user' => 'user',
                'pass' => 'pass',
                'vhost' => '/'
        );
        
        return new AMQPBackendDispatcher($settings, $queues);
    }
    
    public function testQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->once());
        $mock2 = $this->getMockQueue('bar', 'message.type.foo', $this->never());
        $dispatcher = $this->getDispatcher();
        $dispatcher->addBackend('foo', $mock);
        $dispatcher->addBackend('bar', $mock2);
        $dispatcher->createAndPublish('message.type.foo', array());
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidQueue()
    {
        $mock = $this->getMockQueue('foo', 'message.type.bar');
        $dispatcher = $this->getDispatcher('foo', 'message.type.bar');
        $dispatcher->addBackend('bar', $mock);
        $dispatcher->createAndPublish('message.type.bar', array());
    }
}
