<?php
namespace Sonata\NotificationBundle\Backend;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Backend\BackendInterface;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Producer side of the rabbitmq backend. 
 */
class AMQPBackendDispatcher implements BackendInterface
{
    protected $settings;
    
    protected $queues;
    
    protected $backends;
    
    protected $channel;

    /**
     * @param array $settings
     * @param array $queues
     */
    public function __construct(array $settings, array $queues, array $backends)
    {
        $this->settings = $settings;
        $this->queues = $queues;
        $this->backends = $backends;
        
        foreach ($this->backends as $backend) {
            $backend->setDispatcher($this);
        }
    }
    
    /**
     * @return \PhpAmqpLib\Channel\AMQPChannel
     */
    public function getChannel()
    {
        if (!$this->channel) {
            $this->connection = new AMQPConnection(
                    $this->settings['host'],
                    $this->settings['port'],
                    $this->settings['user'],
                    $this->settings['pass'],
                    $this->settings['vhost']
            );
    
            $this->channel = $this->connection->channel();
    
            register_shutdown_function(array($this, 'shutdown'));
        }
    
        return $this->channel;
    }
    
    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        $this->getBackend($message->getType())->publish($message);
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
     * Get a backend by message type.
     * 
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
        
        throw new \RuntimeException('Could not find a message backend for the type ' . $type);
    }
    
    /**
     * Get a backend by queue name
     * 
     * @param string $type the queue name
     * @throws \RuntimeException
     */
    public function getBackendByQueue($name) 
    {
        if (isset($this->backends[$name])) {
            return $this->backends[$name];
        }
        
        throw new \RuntimeException('Could not find a message backend for the queue ' . $name);
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
    
    /**
     * @return void
     */
    public function shutdown()
    {
        if ($this->channel) {
            $this->channel->close();
        }
    
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    
}
