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

use Laminas\Diagnostics\Check\AbstractCheck;

/**
 * @final since sonata-project/notification-bundle 3.x
 */
class BackendHealthCheck extends AbstractCheck
{
    /**
     * @var BackendInterface
     */
    protected $backend;

    public function __construct(BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    public function check()
    {
        return $this->backend->getStatus();
    }

    public function getName()
    {
        return 'Sonata Notification Default Backend';
    }

    public function getGroup()
    {
        return 'sonata';
    }
}
