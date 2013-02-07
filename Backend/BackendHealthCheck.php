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

use Liip\Monitor\Check\CheckInterface;
use Liip\Monitor\Result\CheckResult;

class BackendHealthCheck implements CheckInterface
{
    protected $backend;

    /**
     * @param BackendInterface $backend
     */
    public function __construct(BackendInterface $backend)
    {
        $this->backend = $backend;
    }

    /**
     * {@inheritdoc}
     */
    public function check()
    {
        $status = $this->backend->getStatus();

        return new CheckResult($this->getName(), $status->getMessage(), $status->getStatus());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return "Sonata Notification Default Backend";
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return 'sonata';
    }
}
