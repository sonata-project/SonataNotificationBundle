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
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\BackendInterface;
use Sonata\NotificationBundle\Event\DoctrineOptimizeListener;
use Sonata\NotificationBundle\Event\IterateEvent;
use Sonata\NotificationBundle\Iterator\MessageIteratorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrineOptimizeListenerTest extends TestCase
{
    public function testWithClosedManager()
    {
        $this->expectException(\RuntimeException::class);

        $manager = $this->createMock(EntityManager::class);
        $manager->expects($this->once())->method('isOpen')->will($this->returnValue(false));

        $registry = $this->createMock(RegistryInterface::class);
        $registry->expects($this->once())->method('getManagers')->will($this->returnValue([
            'default' => $manager,
        ]));

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->createMock(MessageIteratorInterface::class),
            $this->createMock(BackendInterface::class)
        ));
    }

    public function testOptimize()
    {
        $unitofwork = $this->createMock(UnitOfWork::class);
        $unitofwork->expects($this->once())->method('clear');

        $manager = $this->createMock(EntityManager::class);
        $manager->expects($this->once())->method('isOpen')->will($this->returnValue(true));
        $manager->expects($this->once())->method('getUnitOfWork')->will($this->returnValue($unitofwork));

        $registry = $this->createMock(RegistryInterface::class);
        $registry->expects($this->once())->method('getManagers')->will($this->returnValue([
            'default' => $manager,
        ]));

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->createMock(MessageIteratorInterface::class),
            $this->createMock(BackendInterface::class)
        ));
    }
}
