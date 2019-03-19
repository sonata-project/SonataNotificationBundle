Usage
=====

Calling an existing consumer
----------------------------

.. code-block:: php

    // retrieve the notification backend
    $backend = $container->get('sonata.notification.backend');

    // create and publish a message
    $backend->createAndPublish('mailer', [
        'from' => [
            'email' => 'no-reply@sonata-project.org',
            'name'  => 'No Reply',
        ],
        'to' => [
            'myuser@example.org' => 'My User',
            'myuser1@example.org' => 'My User 1',
        ],
        'message' => [
            'html' => '<b>hello</b>',
            'text' => 'hello',
        ],
        'subject' => 'Contact form',
        'attachment' => [
            'file' => '/path/to/file',
            'name' => 'fileName',
        ],
    ]);

Custom consumer
----------------

In order to create a consumer, you have to take these two steps :

* Create a consumer class
* Define the consumer in the service container

The consumer class must implement the ``ConsumerInterface`` interface, which defines
only one method ``process``. The ``process`` method will receive a ``ConsumerEvent`` as an
argument. The ``ConsumerEvent`` object is a standard Symfony Event from the ``EventDispatcher``
Component. So it is possible to stop the event propagation from the consumer.

The current example is not meant to be used in production, however it is a good example of
logger consumer creation::

    namespace Sonata\NotificationBundle\Consumer;

    use Sonata\NotificationBundle\Consumer\ConsumerInterface;
    use Sonata\NotificationBundle\Model\MessageInterface;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;

    final class LoggerConsumer implements ConsumerInterface
    {
        private $logger;

        private $types = [
            'emerg',
            'alert',
            'crit',
            'err',
            'warn',
            'notice',
            'info',
            'debug',
        ];

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        public function process(ConsumerEvent $event)
        {
            $message = $event->getMessage();

            if (!in_array($message->getValue('level'), $this->types)) {
                throw new \RuntimeException('Invalid parameter');
            }

            call_user_func([$this->logger, $message->getValue('level')], $message->getValue('message'));
        }
    }

The last step is to register the service as a consumer in the service container. This must be done by using
a custom tag : ``sonata.notification.consumer`` with a ``type``. The ``type`` value is the name used when a
message is receive or created.

.. configuration-block::

    .. code-block:: xml

        <!-- config/services.xml -->

        <service id="sonata.notification.consumer.logger" class="Sonata\NotificationBundle\Consumer\LoggerConsumer">
            <argument type="service" id="logger" />
            <tag name="sonata.notification.consumer" type="logger" />
        </service>

    .. code-block:: yaml

        # config/services.yaml

        services:
            sonata.notification.consumer.logger:
                class: Sonata\NotificationBundle\Consumer\LoggerConsumer
                arguments: ['@logger']
                tags:
                    - { name: sonata.notification.consumer, type: logger }

Now you can use the created service to send a message to the Symfony logger::

    $this->get('sonata.notification.backend')->createAndPublish('logger', [
        'level' => 'debug',
        'message' => 'Hello world!',
    ]);

