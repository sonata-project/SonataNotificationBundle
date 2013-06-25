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

class Message implements MessageInterface
{
    protected $type;

    protected $body;

    protected $state;

    protected $restartCount = 0;

    protected $group;

    protected $createdAt;

    protected $updatedAt;

    protected $startedAt;

    protected $completedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->state = self::STATE_OPEN;
    }

    public function __clone()
    {
        $this->state = self::STATE_OPEN;
        $this->startedAt = null;
        $this->completedAt = null;

        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function setBody(array $body)
    {
        $this->body = $body;
    }

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($names, $default = null)
    {
        if (!is_array($names)) {
            $names = array($names);
        }

        $body = $this->getBody();
        foreach ($names as $name) {
            if (!isset($body[$name])) {
                return $default;
            }

            $body = $body[$name];
        }

        return $body;
    }

    /**
     * {@inheritdoc}
     */
    public function setCompletedAt(\DateTime $completedAt = null)
    {
        $this->completedAt = $completedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function setRestartCount($restartCount)
    {
        $this->restartCount = $restartCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getRestartCount()
    {
        return $this->restartCount;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public static function getStateList()
    {
        return array(
            self::STATE_OPEN            => 'open',
            self::STATE_IN_PROGRESS     => 'in_progress',
            self::STATE_DONE            => 'done',
            self::STATE_ERROR           => 'error',
            self::STATE_CANCELLED       => 'cancelled'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setStartedAt(\DateTime $startedAt = null)
    {
        $this->startedAt = $startedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getStateName()
    {
        $list = self::getStateList();

        return isset($list[$this->getState()]) ? $list[$this->getState()] : '';
    }

    /**
     * {@inheritdoc}
     */
    public function isRunning()
    {
        return $this->state == self::STATE_IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function isError()
    {
        return $this->state == self::STATE_ERROR;
    }

    /**
     * {@inheritdoc}
     */
    public function isOpen()
    {
        return $this->state == self::STATE_OPEN;
    }
}
