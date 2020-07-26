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

namespace Sonata\NotificationBundle\Tests\Notification;

use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\MessageManagerBackend;
use Sonata\NotificationBundle\Exception\HandlingException;
use Sonata\NotificationBundle\Model\MessageInterface;
use Sonata\NotificationBundle\Model\MessageManagerInterface;
use Sonata\NotificationBundle\Tests\Entity\Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MessageManagerBackendTest extends TestCase
{
    public function testCreateAndPublish(): void
    {
        $message = new Message();
        $modelManager = $this->createMock(MessageManagerInterface::class);
        $modelManager->expects($this->once())->method('save')->willReturn($message);
        $modelManager->expects($this->once())->method('create')->willReturn($message);

        $backend = new MessageManagerBackend($modelManager, []);
        $message = $backend->createAndPublish('foo', ['message' => 'salut']);

        $this->assertInstanceOf(MessageInterface::class, $message);
        $this->assertSame(MessageInterface::STATE_OPEN, $message->getState());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertSame('foo', $message->getType());
        $this->assertSame(['message' => 'salut'], $message->getBody());
    }

    public function testHandleSuccess(): void
    {
        $message = new Message();
        $modelManager = $this->createMock(MessageManagerInterface::class);
        $modelManager->expects($this->exactly(2))->method('save')->willReturn($message);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch');

        $backend = new MessageManagerBackend($modelManager, []);

        $backend->handle($message, $dispatcher);

        $this->assertSame(MessageInterface::STATE_DONE, $message->getState());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertNotNull($message->getCompletedAt());
    }

    public function testHandleError(): void
    {
        $message = new Message();
        $modelManager = $this->createMock(MessageManagerInterface::class);
        $modelManager->expects($this->exactly(2))->method('save')->willReturn($message);

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->expects($this->once())->method('dispatch')->will($this->throwException(new \RuntimeException()));
        $backend = new MessageManagerBackend($modelManager, []);

        $e = false;

        try {
            $backend->handle($message, $dispatcher);
        } catch (HandlingException $e) {
        }

        $this->assertInstanceOf(HandlingException::class, $e);

        $this->assertSame(MessageInterface::STATE_ERROR, $message->getState());
        $this->assertNotNull($message->getCreatedAt());
        $this->assertNotNull($message->getCompletedAt());
    }

    /**
     * @dataProvider statusProvider
     */
    public function testStatus($counts, $expectedStatus, $message): void
    {
        $modelManager = $this->createMock(MessageManagerInterface::class);
        $modelManager->expects($this->once())->method('countStates')->willReturn($counts);

        $backend = new MessageManagerBackend($modelManager, [
            MessageInterface::STATE_IN_PROGRESS => 10,
            MessageInterface::STATE_ERROR => 30,
            MessageInterface::STATE_OPEN => 100,
            MessageInterface::STATE_DONE => 10000,
        ]);

        $status = $backend->getStatus();

        $this->assertInstanceOf(\get_class($expectedStatus), $status);
        $this->assertSame($message, $status->getMessage());
    }

    public static function statusProvider(): array
    {
        if (!class_exists(Success::class)) {
            return [[1, 1, 1]];
        }

        $data = [];

        $data[] = [
            [
                MessageInterface::STATE_IN_PROGRESS => 11, //here
                MessageInterface::STATE_ERROR => 31,
                MessageInterface::STATE_OPEN => 100,
                MessageInterface::STATE_DONE => 10000,
            ],
            new Failure(),
            'Too many messages processed at the same time (Database)',
        ];

        $data[] = [
            [
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 31, //here
                MessageInterface::STATE_OPEN => 100,
                MessageInterface::STATE_DONE => 10000,
            ],
            new Failure(),
            'Too many errors (Database)',
        ];

        $data[] = [
            [
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 1,
                MessageInterface::STATE_OPEN => 101, //here
                MessageInterface::STATE_DONE => 10000,
            ],
            new Warning(),
            'Too many messages waiting to be processed (Database)',
        ];

        $data[] = [
            [
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 1,
                MessageInterface::STATE_OPEN => 100,
                MessageInterface::STATE_DONE => 10001, //here
            ],
                new Warning(),
            'Too many processed messages, please clean the database (Database)',
        ];

        $data[] = [
            [
                MessageInterface::STATE_IN_PROGRESS => 1,
                MessageInterface::STATE_ERROR => 1,
                MessageInterface::STATE_OPEN => 1,
                MessageInterface::STATE_DONE => 1,
            ],
            new Success(),
            'Ok (Database)',
        ];

        return $data;
    }
}
