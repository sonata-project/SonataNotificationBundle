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

namespace Sonata\NotificationBundle\Model;

use Sonata\DatagridBundle\Pager\PageableInterface;
use Sonata\Doctrine\Model\ManagerInterface;

interface MessageManagerInterface extends ManagerInterface, PageableInterface
{
    /**
     * @return array
     */
    public function countStates();

    /**
     * @param int $maxAge
     */
    public function cleanup($maxAge);

    /**
     * Cancels a given Message.
     */
    public function cancel(MessageInterface $message);

    /**
     * Restarts a given message (cancels it and returns a new one, ready for publication).
     *
     * @return MessageInterface $message
     */
    public function restart(MessageInterface $message);

    /**
     * @param int $state
     * @param int $batchSize
     *
     * @return MessageInterface[]
     */
    public function findByTypes(array $types, $state, $batchSize);

    /**
     * @param int      $state
     * @param int      $batchSize
     * @param int|null $maxAttempts
     * @param int      $attemptDelay
     *
     * @return mixed
     */
    public function findByAttempts(array $types, $state, $batchSize, $maxAttempts = null, $attemptDelay = 10);
}
