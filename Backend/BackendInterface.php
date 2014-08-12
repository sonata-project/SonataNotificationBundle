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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ZendDiagnostics\Result\ResultInterface;

interface BackendInterface
{
    /**
     * @param MessageInterface $message
     */
    public function publish(MessageInterface $message);

    /**
     * @param string $type
     * @param array  $body
     *
     * @return MessageInterface
     */
    public function create($type, array $body);

    /**
     * @param string $type
     * @param array  $body
     */
    public function createAndPublish($type, array $body);

    /**
     * @return \Sonata\NotificationBundle\Iterator\MessageIteratorInterface
     */
    public function getIterator();

    /**
     * Initialize
     */
    public function initialize();

    /**
     * @param MessageInterface         $message
     * @param EventDispatcherInterface $dispatcher
     *
     * @return mixed
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher);

    /**
     * @return ResultInterface
     */
    public function getStatus();

    /**
     * Clean up messages
     *
     * @return void
     */
    public function cleanup();
}