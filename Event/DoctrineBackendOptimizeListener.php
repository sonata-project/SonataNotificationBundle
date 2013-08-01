<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Event;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Doctrine context optimizer
 * Used with doctrine backend to clear context taking care of the batch iterations
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class DoctrineOptimizeListener
 * @package Sonata\NotificationBundle\Event
 */
class DoctrineBackendOptimizeListener implements IterationListener
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    protected $doctrine;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * Clear the doctrine context if the internal iterator buffer is empty
     *
     * @param IterateEvent $event
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