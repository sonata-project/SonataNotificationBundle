UPGRADE FROM 3.x to 4.0
=======================

### Closed API

* `AMQPBackendDispatcher` and `AMQPMessageIterator` classes are final. You cannot extend them.
* `AMQPBackend` properties are private now.

### AMQP

* `AMQPBackendDispatcher::channel` property was removed. Consider removing dependency on it or use `AMQPBackendDispatcher::getContext()` method.
*  `AMQPBackendDispatcher::connection` property was removed. Consider removing dependency it.
*  `AMQPBackendDispatcher::getChannel()` method was removed. Consider removing dependency on it or use `AMQPBackendDispatcher::getContext()` method.
* The first constructor of `AMQPMessageIterator` constructor was removed.
* `AMQPMessageIterator::queue` property was removed. Consider removing dependency on it.
* `AMQPMessageIterator::AMQMessage` property was removed. Consider removing dependency on it or use `Sonata\NotificationBundle\Model\Message::getValue('interopMessage')`.
* `Sonata\NotificationBundle\Model\Message::getValue('AMQMessage')` is not available any more. Consider removing dependency on it or use `Sonata\NotificationBundle\Model\Message::getValue('interopMessage')`.
*  `AMQPBackend::getChannel()` method was removed. Consider removing dependency on it or use `AMQPBackend::getContext()` method.

### Configuration

* `sonata_notification.admin.enabled` is not enabled by default

### Commands

All commands now extend `Symfony\Component\Console\Command\Command` class instead of the deprecated `Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand` class.
