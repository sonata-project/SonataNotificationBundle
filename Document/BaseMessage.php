<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Document;

use Sonata\NotificationBundle\Model\Message;

/**
 *
 * @author Salma Khemiri <chakroun.salma@gmail.com>
 */
class BaseMessage extends Message
{
    /**
     * @var \MongoId
     */
    protected $id;

    /**
     * Override clone in order to avoid duplicating entries in Doctrine.
     */
    public function __clone()
    {
        parent::__clone();

        $this->id = null;
    }

    /**
     * Get id.
     *
     * @return \MongoId $id
     */
    public function getId()
    {
        return $this->id;
    }
}
