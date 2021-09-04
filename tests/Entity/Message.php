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

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Entity\BaseMessage;
use Sonata\NotificationBundle\Model\MessageInterface;

class Message extends BaseMessage
{
    public function setId($id): void
    {
        $this->id = $id;
    }
}

class BaseMessageTest extends TestCase
{
    public function testClone(): void
    {
        $originalMessage = new Message();
        $originalMessage->setId(42);
        $originalMessage->setBody(['body']);
        $originalMessage->setState(MessageInterface::STATE_ERROR);

        $clonedMessage = clone $originalMessage;

        static::assertSame(['body'], $clonedMessage->getBody());
        static::assertSame(MessageInterface::STATE_ERROR, $clonedMessage->getState());
        static::assertNull($clonedMessage->getId());
    }
}
