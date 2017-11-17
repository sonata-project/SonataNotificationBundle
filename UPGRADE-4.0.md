UPGRADE FROM 3.x to 4.0
=======================

### AMQP

* `AMQPBackendDispatcher::channel` property was removed. Consider removing dependency on it or use `AMQPBackendDispatcher::getContext()` method.
*  `AMQPBackendDispatcher::connection` property was removed. Consider removing dependency it.
*  `AMQPBackendDispatcher::getChannel()` method was removed. Consider removing dependency on it or use `AMQPBackendDispatcher::getContext()` method.
* The first constructor of `AMQPMessageIterator` constructor was removed.
* `AMQPMessageIterator::queue` property was removed. Consider removing dependency on it.
* `AMQPMessageIterator::AMQMessage` property was removed. Consider removing dependency on it or use `Sonata\NotificationBundle\Model\Message::getValue('interopMessage')`.
* `Sonata\NotificationBundle\Model\Message::getValue('AMQMessage')` is not available any more. Consider removing dependency on it or use `Sonata\NotificationBundle\Model\Message::getValue('interopMessage')`.
*  `AMQPBackend::getChannel()` method was removed. Consider removing dependency on it or use `AMQPBackend::getContext()` method.



