<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Model;

interface MessageManagerInterface
{
    /**
     * Creates an empty message instance
     *
     * @return MessageInterface
     */
    public function create();

    /**
     * Deletes a message
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function delete(MessageInterface $message);

    /**
     * Finds one message by the given criteria
     *
     * @param array $criteria
     *
     * @return MessageInterface
     */
    public function findOneBy(array $criteria);

    /**
     * Finds one message by the given criteria
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int   $limit
     * @param int   $offset
     *
     * @return MessageInterface
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null);

    /**
     * Returns the message's fully qualified class name
     *
     * @return string
     */
    public function getClass();

    /**
     * Save a message
     *
     * @param MessageInterface $message
     *
     * @return void
     */
    public function save(MessageInterface $message);

    /**
     * @return integer
     */
    public function countStates();

    /**
     * @param $maxAge
     * @return void
     */
    public function cleanup($maxAge);

    /**
     * Cancels a given Message.
     *
     * @param MessageInterface $message
     *
     * @return void
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
}
