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

    protected $buffer = array();

    /**
     * @param \Sonata\NotificationBundle\Model\MessageManagerInterface $messageManager
     * @param string                                                   $type
     * @param int                                                      $pause
     * @param int                                                      $batchSize
     */
    public function __construct(MessageManagerInterface $messageManager, $type = null, $pause = 500000, $batchSize = 10)
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
     * Assign current pointer a message
     */
    protected function setCurrent()
    {
        if(count($this->buffer) === 0) {
            $this->bufferize($this->type);
        }

        $this->current = array_pop($this->buffer);
    }

    /**
     * Fill the inner messages buffer
     *
     * @param string|null $type
     */
    protected function bufferize($type = null)
    {
        while (true) {
            $params = array('state' => MessageInterface::STATE_OPEN);
            if ($type !== null) {
                $params['type'] = $type;
            }

            $this->buffer = $this->messageManager->findBy($params, null, $this->batchSize, null);

            if (count($this->buffer) > 0 ) {
                break;
            }

            usleep($this->pause);
        }
    }
}
