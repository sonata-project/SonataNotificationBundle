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

    protected $type;

    protected $batchSize;

    private $buffer = array();

    /**
     * @param \Sonata\NotificationBundle\Model\MessageManagerInterface $messageManager
     * @param string                                                   $type
     * @param int                                                      $pause
     * @param int                                                      $batchSize
     */
    public function __construct(MessageManagerInterface $messageManager, $type, $pause = 500000, $batchSize = 10)
    {
        $this->messageManager = $messageManager;
        $this->counter        = 0;
        $this->pause          = $pause;
        $this->type           = $type;
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
        while (true) {
            if ($this->setCurrent()) {
                break;
            }
            usleep($this->pause);
        }
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
     * Assign current pointer a message
     * return true if current is assigned
     *
     * @return bool
     */
    protected function setCurrent()
    {
        if(count($this->buffer) === 0) {
            $this->bufferize($this->type);
        }

        if(count($this->buffer) > 0) {
            $this->current = array_pop($this->buffer);

            $this->current->setState(MessageInterface::STATE_IN_PROGRESS);
            $this->messageManager->save($this->current());
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function bufferize($type = null)
    {
        $params = array('state' => MessageInterface::STATE_OPEN);
        if ($type !== null) {
            $params['type'] = $type;
        }

        $this->buffer = $this->messageManager->findBy($params, null, $this->batchSize, null);

        $this->counter += count($this->buffer);
    }
}
