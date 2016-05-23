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

interface MessageInterface
{
    const STATE_OPEN = 0;
    const STATE_IN_PROGRESS = 1;
    const STATE_DONE = 2;
    const STATE_ERROR = -1;
    const STATE_CANCELLED = -2;

    /**
     * @param array $body
     *
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
     *
     * @return mixed
     */
    public function getValue($names, $default = null);

    /**
     * @param \DateTime $completedAt
     */
    public function setCompletedAt(\DateTime $completedAt = null);

    /**
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt = null);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param string $group
     */
    public function setGroup($group);

    /**
     * @return string
     */
    public function getGroup();

    /**
     * @param string $type
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param int $state
     */
    public function setState($state);

    /**
     * @return int
     */
    public function getState();

    /**
     * @param int $restartCount
     */
    public function setRestartCount($restartCount);

    /**
     * @return int
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
     * @param \DateTime $startedAt
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
     * @return bool
     */
    public function isRunning();

    /**
     * @return bool
     */
    public function isCancelled();

    /**
     * @return bool
     */
    public function isError();

    /**
     * @return bool
     */
    public function isOpen();
}
