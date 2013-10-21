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
use Sonata\NotificationBundle\Model\MessageInterface;

class MessageManagerMessageIterator implements MessageIteratorInterface
{
    protected $messageManager;

    protected $counter;

    protected $current;

    protected $types;

    protected $batchSize;

    protected $buffer = array();

    /**
     * @param MessageManagerInterface $messageManager
     * @param array                   $types
     * @param int                     $pause
     * @param int                     $batchSize
     */
    public function __construct(MessageManagerInterface $messageManager, $types = array(), $pause = 500000, $batchSize = 10)
    {
        $this->messageManager = $messageManager;
        $this->counter        = 0;
        $this->pause          = $pause;
        $this->types          = $types;
        $this->batchSize      = $batchSize;
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
        $this->setCurrent();
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
        $this->setCurrent();
    }

    /**
     * Return true if the internal buffer is empty
     *
     * @return bool
     */
    public function isBufferEmpty()
    {
        return 0 === count($this->buffer);
    }

    /**
     * Assign current pointer a message
     */
    protected function setCurrent()
    {
        if(count($this->buffer) === 0) {
            $this->bufferize($this->types);
        }

        $this->current = array_pop($this->buffer);
    }

    /**
     * Fill the inner messages buffer
     *
     * @param array $types
     */
    protected function bufferize($types = array())
    {
        while (true) {
            $this->buffer = $this->findNextMessages($types);

            if (count($this->buffer) > 0 ) {
                break;
            }

            usleep($this->pause);
        }
    }

    /**
     * Find open messages
     *
     * @param array $types
     *
     * @return mixed
     */
    protected function findNextMessages($types)
    {
        return $this->messageManager->findByTypes($types, MessageInterface::STATE_OPEN, $this->batchSize);
    }
}