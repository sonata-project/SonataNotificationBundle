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

use Sonata\NotificationBundle\Entity\BaseMessage;
use Sonata\NotificationBundle\Model\MessageInterface;

class Message extends BaseMessage
{
    public function setId($id)
    {
        $this->id = $id;
    }
}

class BaseMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testClone()
    {
        $originalMessage = new Message();
        $originalMessage->setId(42);
        $originalMessage->setBody("body");
        $originalMessage->setState(MessageInterface::STATE_ERROR);

        $clonedMessage = clone $originalMessage;

        $this->assertEquals("body", $clonedMessage->getBody());
        $this->assertEquals(MessageInterface::STATE_ERROR, $clonedMessage->getState());
        $this->assertNull($clonedMessage->getId());
    }
}