<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Backend;

use ZendDiagnostics\Check\AbstractCheck;

class BackendHealthCheck extends AbstractCheck
{
    /**
     * @var BackendInterface
     */
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
        return $this->backend->getStatus();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Sonata Notification Default Backend';
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup()
    {
        return 'sonata';
    }
}
