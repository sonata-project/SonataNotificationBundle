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
     *
     * @return MessageInterface
     */
    public function findBy(array $criteria);

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
     * Returns the next open message available in the stack
     *
     * @param  int              $pause
     * @return MessageInterface
     */
    public function getNextOpenMessage($pause = 500000);

    /**
     * @return integer
     */
    public function countStates();

    /**
     * @param $maxAge
     * @return void
     */
    public function cleanup($maxAge);
}
