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

class ModelManagerProducerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getBodyValues
     */
    public function testGetValue($body, $names, $expected, $default)
    {
        $message = new Message();

        $message->setBody($body);

        $this->assertEquals($expected, $message->getValue($names, $default));
    }

    public function testClone()
    {
        $message = new Message;
        $message->setId(42);
        $message->setState(Message::STATE_ERROR);

        $this->assertTrue($message->isError());
        $this->assertEquals(42, $message->getId());

        $newMessage = clone $message;

        $this->assertTrue($newMessage->isOpen());
        $this->assertNull($newMessage->getId());
    }

    public function testStatuses()
    {
        $message = new Message;

        $message->setState(MessageInterface::STATE_IN_PROGRESS);
        $this->assertTrue($message->isRunning());

        $message->setState(MessageInterface::STATE_CANCELLED);
        $this->assertTrue($message->isCancelled());

        $message->setState(MessageInterface::STATE_ERROR);
        $this->assertTrue($message->isError());

        $message->setState(MessageInterface::STATE_OPEN);
        $this->assertTrue($message->isOpen());
    }

    /**
     * @return array
     */
    public function getBodyValues()
    {
        return array(
            array(array('name' => 'foobar'), array('name'), 'foobar', null),
            array(array('name' => 'foobar'), array('fake'), 'bar', 'bar'),
            array(array('name' => array('foo' => 'bar')), array('name', 'foo'), 'bar', null),
        );
    }
}
