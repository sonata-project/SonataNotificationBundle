<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Event;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Doctrine context optimizer
 * Used with doctrine backend to clear context taking care of the batch iterations.
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 */
class DoctrineBackendOptimizeListener implements IterationListener
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function iterate(IterateEvent $event)
    {
        if (!method_exists($event->getIterator(), 'isBufferEmpty')) {
            throw new \LogicException('You can\'t use DoctrineOptimizeListener with this iterator');
        }

        if ($event->getIterator()->isBufferEmpty()) {
            $this->doctrine->getManager()->getUnitOfWork()->clear();
        }
    }
}
