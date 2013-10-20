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

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Doctrine context optimizer
 * Used to clear the doctrine context on each command iteration.
 * Do not use with doctrine backend, use DoctrineBackendOptimizeListener instead
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 *
 * Class DoctrineOptimizeListener
 * @package Sonata\NotificationBundle\Event
 */
class DoctrineOptimizeListener implements IterationListener
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
        foreach($this->doctrine->getManagers() as $name => $manager) {
            if (!$manager->isOpen()) {
                throw new \RuntimeException(sprintf('The doctrine manager: %s is closed', $name));
            }

            $manager->getUnitOfWork()->clear();
        }
    }
}