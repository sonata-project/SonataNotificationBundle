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

use Sonata\NotificationBundle\Entity\MessageManager;
use Sonata\NotificationBundle\Model\MessageInterface;

class MessageManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCancel()
    {
        $manager = $this->getMessageManagerMock();

        $message = $this->getMessage();

        $manager->cancel($message);

        $this->assertTrue($message->isCancelled());
    }

    public function testRestart()
    {
        $manager = $this->getMessageManagerMock();

        // test un-restartable status
        $this->assertNull($manager->restart($this->getMessage(MessageInterface::STATE_OPEN)));
        $this->assertNull($manager->restart($this->getMessage(MessageInterface::STATE_CANCELLED)));
        $this->assertNull($manager->restart($this->getMessage(MessageInterface::STATE_IN_PROGRESS)));

        $message = $this->getMessage(MessageInterface::STATE_ERROR);
        $message->setRestartCount(12);

        $newMessage = $manager->restart($message);

        $this->assertEquals(MessageInterface::STATE_OPEN, $newMessage->getState());
        $this->assertEquals(13, $newMessage->getRestartCount());
    }

    public function testGetPager()
    {
        $self = $this;
        $this
            ->getMessageManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with(array());
                $qb->expects($self->once())->method('orderBy')->with(
                    $self->equalTo('m.type'),
                    $self->equalTo('ASC')
                );
            })
            ->getPager(array(), 1);
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Invalid sort field 'invalid' in 'Sonata\NotificationBundle\Entity\BaseMessage' class
     */
    public function testGetPagerWithInvalidSort()
    {
        $self = $this;
        $this
            ->getMessageManager(function ($qb) use ($self) { })
            ->getPager(array(), 1, 10, array('invalid' => 'ASC'));
    }

    public function testGetPagerWithMultipleSort()
    {
        $self = $this;
        $this
            ->getMessageManager(function ($qb) use ($self) {
                $qb->expects($self->never())->method('andWhere');
                $qb->expects($self->once())->method('setParameters')->with(array());
                $qb->expects($self->exactly(2))->method('orderBy')->with(
                    $self->logicalOr(
                        $self->equalTo('m.type'),
                        $self->equalTo('m.state')
                    ),
                    $self->logicalOr(
                        $self->equalTo('ASC'),
                        $self->equalTo('DESC')
                    )
                );
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array()));
            })
            ->getPager(array(), 1, 10, array(
                'type' => 'ASC',
                'state' => 'DESC',
            ));
    }

    public function testGetPagerWithOpenedMessages()
    {
        $self = $this;
        $this
            ->getMessageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('m.state = :state'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('state' => MessageInterface::STATE_OPEN)));
            })
            ->getPager(array('state' => MessageInterface::STATE_OPEN), 1);
    }

    public function testGetPagerWithCanceledMessages()
    {
        $self = $this;
        $this
            ->getMessageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('m.state = :state'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('state' => MessageInterface::STATE_CANCELLED)));
            })
            ->getPager(array('state' => MessageInterface::STATE_CANCELLED), 1);
    }

    public function testGetPagerWithInProgressMessages()
    {
        $self = $this;
        $this
            ->getMessageManager(function ($qb) use ($self) {
                $qb->expects($self->once())->method('andWhere')->with($self->equalTo('m.state = :state'));
                $qb->expects($self->once())->method('setParameters')->with($self->equalTo(array('state' => MessageInterface::STATE_IN_PROGRESS)));
            })
            ->getPager(array('state' => MessageInterface::STATE_IN_PROGRESS), 1);
    }

    /**
     * @return \Sonata\NotificationBundle\Tests\Entity\MessageManagerMock
     */
    protected function getMessageManagerMock()
    {
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $manager = new MessageManagerMock('Sonata\notificationBundle\Tests\Entity\Message', $registry);

        return $manager;
    }

    /**
     * @return \Sonata\NotificationBundle\Entity\MessageManager
     */
    protected function getMessageManager($qbCallback)
    {
        $query = $this->getMockForAbstractClass('Doctrine\ORM\AbstractQuery', array(), '', false, true, true, array('execute'));
        $query->expects($this->any())->method('execute')->will($this->returnValue(true));

        $qb = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(
            $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock(),
        ));

        $qb->expects($this->any())->method('select')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('getQuery')->will($this->returnValue($query));

        $qbCallback($qb);

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
        $repository->expects($this->any())->method('createQueryBuilder')->will($this->returnValue($qb));

        $metadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $metadata->expects($this->any())->method('getFieldNames')->will($this->returnValue(array(
            'state',
            'type',
        )));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $em->expects($this->any())->method('getRepository')->will($this->returnValue($repository));
        $em->expects($this->any())->method('getClassMetadata')->will($this->returnValue($metadata));

        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())->method('getManagerForClass')->will($this->returnValue($em));

        return  new MessageManager('Sonata\NotificationBundle\Entity\BaseMessage', $registry);
    }

    /**
     * @return \Sonata\NotificationBundle\Tests\Entity\Message
     */
    protected function getMessage($state = MessageInterface::STATE_OPEN)
    {
        $message = new Message();

        $message->setState($state);

        return $message;
    }
}
