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

use Sonata\NotificationBundle\Model\MessageInterface;

class MessageManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testCancel()
    {
        $manager = $this->getMessageManager();

        $message = $this->getMessage();

        $manager->cancel($message);

        $this->assertTrue($message->isCancelled());
    }

    public function testRestart()
    {
        $manager = $this->getMessageManager();

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

    /**
     * @return \Sonata\NotificationBundle\Entity\MessageManager
     */
    protected function getMessageManager()
    {
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $manager = new MessageManagerMock('Sonata\notificationBundle\Tests\Entity\Message', $registry);

        return $manager;
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
