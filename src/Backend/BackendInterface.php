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

namespace Sonata\NotificationBundle\Backend;

use Laminas\Diagnostics\Result\ResultInterface;
use Sonata\NotificationBundle\Iterator\MessageIteratorInterface;
use Sonata\NotificationBundle\Model\MessageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface BackendInterface
{
    public function publish(MessageInterface $message);

    /**
     * @param string $type
     *
     * @return MessageInterface
     */
    public function create($type, array $body);

    /**
     * @param string $type
     */
    public function createAndPublish($type, array $body);

    /**
     * @return MessageIteratorInterface
     */
    public function getIterator();

    /**
     * Initialize.
     *
     * @return void
     */
    public function initialize();

    /**
     * @return mixed
     */
    public function handle(MessageInterface $message, EventDispatcherInterface $dispatcher);

    /**
     * @return ResultInterface
     */
    public function getStatus();

    /**
     * Clean up messages.
     *
     * @return void
     */
    public function cleanup();
}
