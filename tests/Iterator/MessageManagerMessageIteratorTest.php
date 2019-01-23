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

namespace Sonata\NotificationBundle\Tests\Iterator;

use Doctrine\Common\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Iterator\MessageManagerMessageIterator as MessageManagerMessageIteratorObject;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Model\MessageManagerInterface;

/**
 * @author Kevin Nedelec <kevin.nedelec@ekino.com>
 */
class MessageManagerMessageIteratorTest extends TestCase
{
    /**
     * @var ManagerRegistry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
    }

    public function testBufferize(): void
    {
        $iterator = new MessageManagerMessageIterator($this->registry, 0);

        $iterator->_bufferize();

        $this->assertCount(10, $iterator->getBuffer());
    }

    public function testIterations(): void
    {
        $size = 10;

        $iterator = new MessageManagerMessageIterator($this->registry, 0);

        $iterator->rewind();
        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());

        $iterator->next();
        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());

        --$size;
        while (--$size >= 1) {
            $iterator->next();
        }

        $this->assertTrue($iterator->valid());
        $this->assertNotNull($iterator->current());
    }

    public function testLongForeach(): void
    {
        $iterator = new MessageManagerMessageIterator($this->registry, 500000, 2);

        $count = 0;

        foreach ($iterator as $message) {
            ++$count;
            $this->assertNotNull($message);
            if ($count > 20) {
                return;
            }
        }
    }

    public function testMessageConsumptionOrder(): void
    {
        $now = new \DateTime();
        $olderDate = (clone $now)->modify('-1 hour');
        $oldMessage = new Message();
        $oldMessage->setCreatedAt($olderDate);
        $oldMessage->setType('older.message');

        $newMessage = new Message();
        $newMessage->setCreatedAt($now);
        $newMessage->setType('newer.message');

        $messageManager = $this->createMock(MessageManagerInterface::class);
        $messageManager->expects($this->once())->method('findByTypes')->willReturn([$oldMessage, $newMessage]);

        $iterator = new MessageManagerMessageIteratorObject($messageManager, [], 500000, 2);

        $iterator->next();
        $this->assertSame($oldMessage, $iterator->current());
        $iterator->next();
        $this->assertSame($newMessage, $iterator->current());
    }
}
