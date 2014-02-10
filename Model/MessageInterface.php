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

interface MessageInterface
{
    const STATE_OPEN = 0;
    const STATE_IN_PROGRESS = 1;
    const STATE_DONE = 2;
    const STATE_ERROR = -1;
    const STATE_CANCELLED = -2;

    /**
     * @param  array $body
     * @return array
     */
    public function setBody(array $body);

    /**
     * @return array
     */
    public function getBody();

    /**
     * @param array|string $names
     * @param $default
     * @return mixed
     */
    public function getValue($names, $default = null);

    /**
     * @param  \DateTime $completedAt
     * @return void
     */
    public function setCompletedAt(\DateTime $completedAt = null);

    /**
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * @param  \DateTime $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param  string $group
     * @return void
     */
    public function setGroup($group);

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @param  string $type
     * @return void
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param  integer $state
     * @return void
     */
    public function setState($state);

    /**
     * @return integer
     */
    public function getState();

    /**
     * @param integer $restartCount
     */
    public function setRestartCount($restartCount);

    /**
     * @return integer
     */
    public function getRestartCount();

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param  \DateTime $startedAt
     * @return void
     */
    public function setStartedAt(\DateTime $startedAt = null);

    /**
     * @return \DateTime
     */
    public function getStartedAt();

    /**
     * @return string
     */
    public function getStateName();

    /**
     * @return boolean
     */
    public function isRunning();

    /**
     * @return boolean
     */
    public function isCancelled();

    /**
     * @return boolean
     */
    public function isError();

    /**
     * @return boolean
     */
    public function isOpen();
}
