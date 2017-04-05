<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Entity;

use Sonata\NotificationBundle\Event\DoctrineOptimizeListener;
use Sonata\NotificationBundle\Event\IterateEvent;
use Sonata\NotificationBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

class DoctrineOptimizeListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testWithClosedManager()
    {
        $manager = $this->createMock('Doctrine\ORM\EntityManager');
        $manager->expects($this->once())->method('isOpen')->will($this->returnValue(false));

        $registry = $this->createMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $registry->expects($this->once())->method('getManagers')->will($this->returnValue(array(
            'default' => $manager,
        )));

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->getMock('Sonata\NotificationBundle\Iterator\MessageIteratorInterface'),
            $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface')
        ));
    }

    public function testOptimize()
    {
        $unitofwork = $this->createMock('Doctrine\ORM\UnitOfWork');
        $unitofwork->expects($this->once())->method('clear');

        $manager = $this->createMock('Doctrine\ORM\EntityManager');
        $manager->expects($this->once())->method('isOpen')->will($this->returnValue(true));
        $manager->expects($this->once())->method('getUnitOfWork')->will($this->returnValue($unitofwork));

        $registry = $this->createMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $registry->expects($this->once())->method('getManagers')->will($this->returnValue(array(
            'default' => $manager,
        )));

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->createMock('Sonata\NotificationBundle\Iterator\MessageIteratorInterface'),
            $this->createMock('Sonata\NotificationBundle\Backend\BackendInterface')
        ));
    }
}
