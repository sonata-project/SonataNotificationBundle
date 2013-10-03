Usage
=====

Calling an existing consumer
----------------------------

.. code-block:: php

    <?php
    // retrieve the notification backend
    $backend = $container->get('sonata.notification.backend');

    // create and publish a message
    $backend->createAndPublish('mailer', array(
        'from' => array(
            'email' => 'no-reply@sonata-project.org',
            'name'  => 'No Reply'
        ),
        'to'   => array(
            'myuser@example.org' => 'My User',
            'myuser1@example.org' => 'My User 1',
        ),
        'message' => array(
            'html' => '<b>hello</b>',
            'text' => 'hello'
        ),
        'subject' => 'Contact form',
    ));


Custom consumer
----------------

In order to create a consumer, 2 step must be done :

* Create a consumer class
* Define the consumer into the service container


The consumer class must implement a ``ConsumerInterface`` interface, which defines
only one method ``process``. The ``process`` method will get a ``ConsumerEvent`` as
argument. The ``ConsumerEvent`` object is a standard Symfony Event from the ``EventDispatcher``
Component. So it is possible to stop the event propagation from the a consumer.

The current exemple does not mean to be used in production, however it is a good exemple about
how to create a logger consumer.

.. code-block:: php

    <?php
    namespace Sonata\NotificationBundle\Consumer;

    use Sonata\NotificationBundle\Model\MessageInterface;
    use Symfony\Component\HttpKernel\Log\LoggerInterface;
    use Sonata\NotificationBundle\Exception\InvalidParameterException;

    class LoggerConsumer implements ConsumerInterface
    {
        protected $logger;

        protected $types = array(
            'emerg',
            'alert',
            'crit',
            'err',
            'warn',
            'notice',
            'info',
            'debug',
        );

        /**
         * @param \Symfony\Component\HttpKernel\Log\LoggerInterface $logger
         */
        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        /**
         * {@inheritdoc}
         */
        public function process(ConsumerEvent $event)
        {
            $message = $event->getMessage();

            if (!in_array($message->getValue('level'), $this->types)) {
                throw new InvalidParameterException();
            }

            call_user_func(array($this->logger, $message->getValue('level')), $message->getValue('message'));
        }
    }

The last step is to register the service as a consumer into the service container. This must be done by using
a custom tag : ``sonata.notification.consumer`` with a ``type``. The ``type`` value is the name used when a
message is receive or created.

.. code-block:: xml

    <?xml version="1.0" ?>

    <container xmlns="http://symfony.com/schema/dic/services"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

        <services>
            <service id="sonata.notification.consumer.logger" class="Sonata\NotificationBundle\Consumer\LoggerConsumer">
                <tag name="sonata.notification.consumer" type="logger" />

                <argument type="service" id="logger" />
            </service>
        </services>
    </container>


Now you can use the created service to send a message to the symfony logger.

.. code-block:: php

    <?php
    $this->get('sonata.notification.backend')->createAndPublish('logger', array(
        'level' => 'debug',
        'message' => 'Hello world!'
    ));

