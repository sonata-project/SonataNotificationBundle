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

    /**
     * Publish a message by adding it to the local storage.
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function publish(MessageInterface $message)
    {
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
    	if (is_array($this->messages)) {
        	foreach ($this->messages as $eachMessage) {
            	$this->handle($eachMessage, $this->dispatcher);
        	}
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
