<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Model;

use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\CoreBundle\Model\PageableManagerInterface;

interface MessageManagerInterface extends ManagerInterface, PageableManagerInterface
{
    /**
     * @return int
     */
    public function countStates();

    /**
     * @param $maxAge
     */
    public function cleanup($maxAge);

    /**
     * Cancels a given Message.
     *
     * @param MessageInterface $message
     */
    public function cancel(MessageInterface $message);

    /**
     * Restarts a given message (cancels it and returns a new one, ready for publication).
     *
     * @param MessageInterface $message
     *
     * @return MessageInterface $message
     */
    public function restart(MessageInterface $message);

    /**
     * @param array $types
     * @param int   $state
     * @param int   $batchSize
     *
     * @return MessageInterface[]
     */
    public function findByTypes(array $types, $state, $batchSize);

    /**
     * @param array $types
     * @param       $state
     * @param       $batchSize
     * @param null  $maxAttempts
     * @param int   $attemptDelay
     *
     * @return mixed
     */
    public function findByAttempts(array $types, $state, $batchSize, $maxAttempts = null, $attemptDelay = 10);
}
