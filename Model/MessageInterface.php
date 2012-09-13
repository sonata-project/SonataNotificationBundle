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

    /**
     * @param array $body
     * @return array
     */
    function setBody(array $body);

    /**
     * @return array
     */
    function getBody();

    /**
     * @param array|string $names
     * @param $default
     * @return mixed
     */
    function getValue($names, $default = null);

    /**
     * @param \DateTime $completedAt
     * @return void
     */
    function setCompletedAt(\DateTime $completedAt);

    /**
     * @return \DateTime
     */
    function getCompletedAt();

    /**
     * @param \DateTime $createdAt
     * @return void
     */
    function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    function getCreatedAt();

    /**
     * @param string $group
     * @return void
     */
    function setGroup($group);

    /**
     * @return string
     */
    function getGroup();

    /**
     * @param string $type
     * @return void
     */
    function setType($type);

    /**
     * @return string
     */
    function getType();

    /**
     * @param integer $state
     * @return void
     */
    function setState($state);

    /**
     * @return integer
     */
    function getState();

    /**
     * @param integer $restartCount
     * @return void
     */
    function setRestartCount($restartCount);

    /**
     * @return integer
     */
    function getRestartCount();

    /**
     * @param \DateTime $updatedAt
     * @return void
     */
    function setUpdatedAt(\DateTime $updatedAt);

    /**
     * @return \DateTime
     */
    function getUpdatedAt();

    /**
     * @param \DateTime $startedAt
     * @return void
     */
    function setStartedAt(\DateTime $startedAt);

    /**
     * @return \DateTime
     */
    function getStartedAt();
}

