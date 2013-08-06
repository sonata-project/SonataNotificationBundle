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

use Doctrine\ORM\EntityManager;
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
    public function __construct(EntityManager $messageManager, $pause = 0, $batchSize = 10)
    {
        parent::__construct(
            new MessageManagerMock($messageManager, 'Sonata\NotificationBundle\Model\Message'),
            array(),
            $pause,
            $batchSize);
    }

    /**
     * @param null $types
     */
    public function _bufferize($types = array())
    {
        $this->bufferize($types);
    }

    /**
     * @return array
     */
    public function getBuffer()
    {
        return $this->buffer;
    }
}