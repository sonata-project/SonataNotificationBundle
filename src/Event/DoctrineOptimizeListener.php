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

use Doctrine\Persistence\ManagerRegistry;

/**
 * Doctrine context optimizer
 * Used to clear the doctrine context on each command iteration.
 * Do not use with doctrine backend, use DoctrineBackendOptimizeListener instead.
 *
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 */
class DoctrineOptimizeListener implements IterationListener
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function iterate(IterateEvent $event)
    {
        foreach ($this->doctrine->getManagers() as $name => $manager) {
            if (!$manager->isOpen()) {
                throw new \RuntimeException(sprintf('The doctrine manager: %s is closed', $name));
            }

            $manager->getUnitOfWork()->clear();
        }
    }
}
