<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Iterator;

use Sonata\NotificationBundle\Model\MessageManagerInterface;

class MessageManagerMessageIterator implements MessageIteratorInterface
{
    protected $messageManager;

    protected $counter;

    protected $current;

    /**
     * @param \Sonata\NotificationBundle\Model\MessageManagerInterface $messageManager
     * @param int                                                      $pause
     */
    public function __construct(MessageManagerInterface $messageManager, $pause = 500000)
    {
        $this->messageManager = $messageManager;
        $this->counter        = 0;
        $this->pause          = $pause;
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->current;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $this->current = $this->messageManager->getNextOpenMessage($this->pause);
        $this->counter++;
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return $this->counter;
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        $this->current = $this->messageManager->getNextOpenMessage($this->pause);
    }
}
