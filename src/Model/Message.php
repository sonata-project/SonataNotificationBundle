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

class Message implements MessageInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $body;

    /**
     * @var int
     */
    protected $state;

    /**
     * @var int
     */
    protected $restartCount = 0;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $startedAt;

    /**
     * @var \DateTime
     */
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

    public function setBody(array $body): void
    {
        $this->body = $body;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getValue($names, $default = null)
    {
        if (!\is_array($names)) {
            $names = [$names];
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

    public function setCompletedAt(?\DateTime $completedAt = null): void
    {
        $this->completedAt = $completedAt;
    }

    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    public function setCreatedAt(?\DateTime $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setGroup($group): void
    {
        $this->group = $group;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setState($state): void
    {
        $this->state = $state;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setRestartCount($restartCount): void
    {
        $this->restartCount = $restartCount;
    }

    public function getRestartCount()
    {
        return $this->restartCount;
    }

    public function setUpdatedAt(?\DateTime $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return string[]
     */
    public static function getStateList()
    {
        return [
            self::STATE_OPEN => 'open',
            self::STATE_IN_PROGRESS => 'in_progress',
            self::STATE_DONE => 'done',
            self::STATE_ERROR => 'error',
            self::STATE_CANCELLED => 'cancelled',
        ];
    }

    public function setStartedAt(?\DateTime $startedAt = null): void
    {
        $this->startedAt = $startedAt;
    }

    public function getStartedAt()
    {
        return $this->startedAt;
    }

    public function getStateName()
    {
        $list = self::getStateList();

        return isset($list[$this->getState()]) ? $list[$this->getState()] : '';
    }

    public function isRunning()
    {
        return self::STATE_IN_PROGRESS === $this->state;
    }

    public function isCancelled()
    {
        return self::STATE_CANCELLED === $this->state;
    }

    public function isError()
    {
        return self::STATE_ERROR === $this->state;
    }

    public function isOpen()
    {
        return self::STATE_OPEN === $this->state;
    }
}
