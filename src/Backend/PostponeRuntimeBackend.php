<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Backend;

use Sonata\NotificationBundle\Iterator\IteratorProxyMessageIterator;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ZendDiagnostics\Result\Success;

/**
 * This backend postpones the handling of messages to a registered event.
 *
 * It's based on the asynchronous event dispatcher:
 *
 * @see https://gist.github.com/3852361
 *
 * @author Toni Uebernickel <tuebernickel@gmail.com>
 */
class PostponeRuntimeBackend extends RuntimeBackend
{
    /**
     * @var MessageInterface[]
     */
    protected $messages = [];

    /**
     * If set to true, you have to fire an event the onEvent method is subscribed to manually!
     *
     * @var bool
     */
    protected $postponeOnCli = false;

    /**
     * @param EventDispatcherInterface $dispatcher
     * @param bool                     $postponeOnCli Whether to postpone the messages on the CLI, too
     */
    public function __construct(EventDispatcherInterface $dispatcher, $postponeOnCli = false)
    {
        parent::__construct($dispatcher);

        $this->postponeOnCli = $postponeOnCli;
    }

    /**
     * {@inheritdoc}
     */
    public function publish(MessageInterface $message)
    {
        // if the message is generated from the cli the message is handled
        // directly as there is no kernel.terminate in cli
        if (!$this->postponeOnCli && $this->isCommandLineInterface()) {
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
        while (!empty($this->messages)) {
            $message = array_shift($this->messages);

            $this->handle($message, $this->dispatcher);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new IteratorProxyMessageIterator(new \ArrayIterator($this->messages));
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return new Success('Postpone runtime backend', 'Ok (Postpone Runtime)');
    }

    /**
     * Check whether this Backend is run on the CLI.
     *
     * @return bool
     */
    protected function isCommandLineInterface()
    {
        return 'cli' === PHP_SAPI;
    }
}
