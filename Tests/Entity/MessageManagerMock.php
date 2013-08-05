<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Entity;

use Sonata\NotificationBundle\Entity\MessageManager;

/**
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class MessageManagerMock
 * @package Sonata\NotificationBundle\Tests\Entity
 */
class MessageManagerMock extends MessageManager
{
    /**
     * @inheritdoc
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $result = array();
        while ($limit !== null && $limit > 0) {
            $result[$limit] = new Message();
            $limit--;
        }

        return $result;
    }

    public function findByTypes(array $types, $state, $batchSize)
    {
        $result = array();
        while ($batchSize !== null && $batchSize > 0) {
            $result[$batchSize] = new Message();
            $batchSize--;
        }

        return $result;
    }
}