<?php
namespace Sonata\NotificationBundle\Backend;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;

/**
 * Producer side of the rabbitmq backend. 
 */
class AMQPBackendDispatcher implements BackendInterface
{
    /**
     * The available queues
     * 
     * @var array
     */
    protected $queues;
    
    /**
     * The available rabbitmq queue backends
     * 
     * @var array
     */
    protected $backends;
    
    /**
     * @param array $queues
     */
    public function __construct(array $queues)
    {
        $this->queues = $queues;
        $this->backends = array();        
    }
    
    /**
     * {@inheritdoc}
     */
    public function addBackend($queue, BackendInterface $backend)
    {
        $this->backends[$queue] = $backend;
    }
    
    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        throw new \RuntimeException('Messages for the rabbitmq backend need to be published via createAndPublish()');

    }
    
    /**
     * {@inheritdoc}
     */
    public function create($type, array $body)
    {
        $this->getBackend($type)->create($type, $body);

    }
    
    public function createAndPublish($type, array $body)
    {
        $this->getBackend($type)->createAndPublish($type, $body);
    }
    
    /**
     * @param string $type
     * @throws \RuntimeException
     * @return BackendInterface
     */
    protected function getBackend($type)
    {
        foreach ($this->queues as $queue) {
            if (isset($this->backends[$queue['queue']]) && $type === $queue['routing_key']) {
                return $this->backends[$queue['queue']];
            }
        }
        
        throw new \RuntimeException('Could not find a message backend for the type ' . $type . ', tried ');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        throw new \RuntimeException('You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.');

    }
    
    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher)
    {
        throw new \RuntimeException('You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.');

    }
    
    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        throw new \RuntimeException('You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.');

    }
    
    /**
     * {@inheritdoc}
     */
    public function cleanup()
    {
        throw new \RuntimeException('You need to use a specific rabbitmq backend supporting the selected queue to run a consumer.');

    }
}
