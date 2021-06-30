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

namespace Sonata\NotificationBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Doctrine context optimizer
 * Used with doctrine backend to clear context taking care of the batch iterations.
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * @final since sonata-project/notification-bundle 3.13
 */
class DoctrineBackendOptimizeListener implements IterationListener
{
    /**
     * @var Registry
     */
    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function iterate(IterateEvent $event): void
    {
        if (!method_exists($event->getIterator(), 'isBufferEmpty')) {
            throw new \LogicException('You can\'t use DoctrineOptimizeListener with this iterator');
        }

        if ($event->getIterator()->isBufferEmpty()) {
            $this->doctrine->getManager()->getUnitOfWork()->clear();
        }
    }
}
