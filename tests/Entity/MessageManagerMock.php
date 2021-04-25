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

namespace Sonata\NotificationBundle\Tests\Entity;

use Sonata\NotificationBundle\Entity\MessageManager;

/**
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 */
class MessageManagerMock extends MessageManager
{
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        $result = [];
        while (null !== $limit && $limit > 0) {
            $result[$limit] = new Message();
            --$limit;
        }

        return $result;
    }

    public function findByTypes(array $types, $state, $batchSize): array
    {
        $result = [];
        while (null !== $batchSize && $batchSize > 0) {
            $result[$batchSize] = new Message();
            --$batchSize;
        }

        return $result;
    }

    public function save($entity, $andFlush = true): void
    {
    }
}
