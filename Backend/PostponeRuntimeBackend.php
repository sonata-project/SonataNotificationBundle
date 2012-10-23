<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Backend;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Sonata\NotificationBundle\Backend\BackendStatus;
use Sonata\NotificationBundle\Iterator\IteratorProxyMessageIterator;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * This backend postpones the handling of messages to a registered event.
 *
 * It's based on the asynchronous event dispatcher:
 * @link https://gist.github.com/3852361
 *
 * @author Toni Uebernickel <tuebernickel@gmail.com>
 */
class PostponeRuntimeBackend extends RuntimeBackend
{
    /**
     * @var MessageInterface[]
     */
    protected $messages;

    protected $sapi;

    /**
     * @param EventDispatcherInterface $dispatcher The symfony event dispatcher
     * @param string                   $sapi       Only used for testing ...
     */
    public function __construct(EventDispatcherInterface $dispatcher, $sapi = null)
    {
        parent::__construct($dispatcher);

        $this->messages = array();


        $this->sapi = $sapi === null ? php_sapi_name() : $sapi;
    }

    /**
     * Publish a message by adding it to the local storage.
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function publish(MessageInterface $message)
    {
        // if the message is generated from the cli the message is handled
        // directly as there is no kernel.terminate in cli
        if (php_sapi_name() === $this->sapi) {
            $this->handle($message, $this->dispatcher);

            return;
        }

        $this->messages[] = $message;
    }

    /**
     * Listen on any event and handle the messages.
     *
     * Actually, an event is not necessary, you can call this method manually, to.
     * The event is not processed in any way.
     *
     * @param Event|null $event
     */
    public function onEvent(Event $event = null)
    {
        foreach ($this->messages as $eachMessage) {
            $this->handle($eachMessage, $this->dispatcher);
        }
    }

    /**
     * @return \Sonata\NotificationBundle\Iterator\MessageIteratorInterface
     */
    public function getIterator()
    {
        return new IteratorProxyMessageIterator(new \ArrayIterator($this->messages));
    }

    /**
     * @return BackendStatus
     */
    public function getStatus()
    {
        return new BackendStatus(BackendStatus::OK, 'Ok (Postpone Runtime)');
    }
}
