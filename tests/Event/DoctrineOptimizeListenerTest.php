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

namespace Sonata\NotificationBundle\Tests\Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Event\DoctrineOptimizeListener;
use Sonata\NotificationBundle\Event\IterateEvent;
use Sonata\NotificationBundle\Iterator\MessageIteratorInterface;

class DoctrineOptimizeListenerTest extends TestCase
{
    public function testWithClosedManager(): void
    {
        $this->expectException(\RuntimeException::class);

        $manager = $this->createMock(EntityManager::class);
        $manager->expects($this->once())->method('isOpen')->willReturn(false);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->once())->method('getManagers')->willReturn([
            'default' => $manager,
        ]);

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->createMock(MessageIteratorInterface::class),
            $this->createMock(BackendInterface::class)
        ));
    }

    public function testOptimize(): void
    {
        $unitofwork = $this->createMock(UnitOfWork::class);
        $unitofwork->expects($this->once())->method('clear');

        $manager = $this->createMock(EntityManager::class);
        $manager->expects($this->once())->method('isOpen')->willReturn(true);
        $manager->expects($this->once())->method('getUnitOfWork')->willReturn($unitofwork);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->once())->method('getManagers')->willReturn([
            'default' => $manager,
        ]);

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->createMock(MessageIteratorInterface::class),
            $this->createMock(BackendInterface::class)
        ));
    }
}
