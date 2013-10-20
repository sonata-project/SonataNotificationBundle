<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Entity;

use Sonata\NotificationBundle\Event\DoctrineOptimizeListener;
use Sonata\NotificationBundle\Event\IterateEvent;

class DoctrineOptimizeListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testWithClosedManager()
    {
        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())->method('isOpen')->will($this->returnValue(false));

        $registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $registry->expects($this->once())->method('getManagers')->will($this->returnValue(array(
            'default' => $manager
        )));

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->getMock('Sonata\NotificationBundle\Iterator\MessageIteratorInterface'),
            $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface')
        ));
    }

    public function testOptimize()
    {
        $unitofwork = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $unitofwork->expects($this->once())->method('clear');

        $manager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $manager->expects($this->once())->method('isOpen')->will($this->returnValue(true));
        $manager->expects($this->once())->method('getUnitOfWork')->will($this->returnValue($unitofwork));

        $registry = $this->getMock('Symfony\Bridge\Doctrine\RegistryInterface');
        $registry->expects($this->once())->method('getManagers')->will($this->returnValue(array(
            'default' => $manager
        )));

        $optimizer = new DoctrineOptimizeListener($registry);
        $optimizer->iterate(new IterateEvent(
            $this->getMock('Sonata\NotificationBundle\Iterator\MessageIteratorInterface'),
            $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface')
        ));
    }
}
