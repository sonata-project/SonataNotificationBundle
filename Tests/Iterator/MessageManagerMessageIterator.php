<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Iterator;

use Sonata\NotificationBundle\Iterator\MessageManagerMessageIterator as Iterator;
use Sonata\NotificationBundle\Tests\Entity\MessageManagerMock;

/**
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class MessageManagerMessageIterator
 * @package Sonata\NotificationBundle\Tests\Iterator
 */
class MessageManagerMessageIterator extends Iterator
{
    public function __construct($pause = 0, $batchSize = 10)
    {
        parent::__construct(
            new MessageManagerMock(),
            null,
            $pause,
            $batchSize);
    }

    /**
     * @param null $type
     */
    public function _bufferize($type = null)
    {
        $this->bufferize($type);
    }

    /**
     * @return array
     */
    public function getBuffer()
    {
        return $this->buffer;
    }
}