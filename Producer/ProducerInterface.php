<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Producer;

use Sonata\NotificationBundle\Model\MessageInterface;

interface ProducerInterface
{
    /**
     * @param \Sonata\NotificationBundle\Model\MessageInterface $message
     * @return void
     */
    function publish(MessageInterface $message);

    /**
     * @param $type
     * @param array $body
     * @return void
     */
    function create($type, array $body);

    /**
     * @param $type
     * @param array $body
     * @return void
     */
    function createAndPublish($type, array $body);
}