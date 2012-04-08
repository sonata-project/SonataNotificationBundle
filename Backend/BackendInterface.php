<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Backend;

use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Iterator\MessageIteratorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface BackendInterface
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

    /**
     * @return \Sonata\NotificationBundle\Iterator\MessageIteratorInterface
     */
    function getIterator();

    /**
     * Initialize
     *
     * @return void
     */
    function initialize();

    /**
     * @param \Sonata\NotificationBundle\Model\MessageInterface $message
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     * @return void
     */
    function handle(MessageInterface $message, EventDispatcherInterface $dispatcher);

    /**
     * @return BackendStatus
     */
    function getStatus();
}