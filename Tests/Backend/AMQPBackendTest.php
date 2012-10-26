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
    
    public function testQueue()
    {
        $queues = array(
            array('queue' => 'foo', 'routing_key' => 'message.type.foo')
        );
        
        $mock = $this->getMockQueue('foo', 'message.type.foo', $this->once());
        $mock2 = $this->getMockQueue('bar', 'message.type.foo', $this->never());
        $dispatcher = new AMQPBackendDispatcher($queues);
        $dispatcher->addBackend('foo', $mock);
        $dispatcher->createAndPublish('message.type.foo', array());
    }
    
    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidQueue()
    {
        $queues = array(
                array('queue' => 'foo', 'routing_key' => 'message.type.bar')
        );
    
        $mock = $this->getMockQueue('foo', 'message.type.bar');
        $dispatcher = new AMQPBackendDispatcher($queues);
        $dispatcher->addBackend('bar', $mock);
        $dispatcher->createAndPublish('message.type.bar', array());
    }    
}
