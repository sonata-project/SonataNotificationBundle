<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Iterator;

class RabbitMQMessageIterator implements MessageIteratorInterface
{
    /**
     * {@inheritDoc}
     */
    public function current()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        throw new \RuntimeException('Not implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        throw new \RuntimeException('Not implemented');
    }
}