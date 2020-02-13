<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Iterator;

use Doctrine\Common\Persistence\ManagerRegistry;
use Sonata\NotificationBundle\Iterator\MessageManagerMessageIterator as Iterator;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Tests\Entity\MessageManagerMock;

/**
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 */
class MessageManagerMessageIterator extends Iterator
{
    public function __construct(ManagerRegistry $registry, $pause = 0, $batchSize = 10)
    {
        parent::__construct(
            new MessageManagerMock(Message::class, $registry),
            [],
            $pause,
            $batchSize
        );
    }

    /**
     * @param array $types
     */
    public function _bufferize($types = []): void
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
