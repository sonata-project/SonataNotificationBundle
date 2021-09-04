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
use Sonata\NotificationBundle\Model\MessageInterface;

class MessageTest extends TestCase
{
    /**
     * @dataProvider getBodyValues
     */
    public function testGetValue($body, $names, $expected, $default): void
    {
        $message = new Message();

        $message->setBody($body);

        static::assertSame($expected, $message->getValue($names, $default));
    }

    public function testClone(): void
    {
        $message = new Message();
        $message->setId(42);
        $message->setState(Message::STATE_ERROR);

        static::assertTrue($message->isError());
        static::assertSame(42, $message->getId());

        $newMessage = clone $message;

        static::assertTrue($newMessage->isOpen());
        static::assertNull($newMessage->getId());
    }

    public function testStatuses(): void
    {
        $message = new Message();

        $message->setState(MessageInterface::STATE_IN_PROGRESS);
        static::assertTrue($message->isRunning());

        $message->setState(MessageInterface::STATE_CANCELLED);
        static::assertTrue($message->isCancelled());

        $message->setState(MessageInterface::STATE_ERROR);
        static::assertTrue($message->isError());

        $message->setState(MessageInterface::STATE_OPEN);
        static::assertTrue($message->isOpen());
    }

    public function getBodyValues(): array
    {
        return [
            [['name' => 'foobar'], ['name'], 'foobar', null],
            [['name' => 'foobar'], ['fake'], 'bar', 'bar'],
            [['name' => ['foo' => 'bar']], ['name', 'foo'], 'bar', null],
        ];
    }
}
