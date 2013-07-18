<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class PullingCommand extends ContainerAwareCommand
{
    /**
     * Clear doctrine unit of work, if used
     */
    protected  function optimize()
    {
        if ($this->getContainer()->has('doctrine')) {
            $this->getContainer()->get('doctrine')->getManager()->getUnitOfWork()->clear();
        }
    }
}