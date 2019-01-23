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

namespace Sonata\NotificationBundle\Tests\Backend;

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\MessageManagerBackend;
use Sonata\NotificationBundle\Backend\MessageManagerBackendDispatcher;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Model\MessageManagerInterface;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class MessageManagerBackendDispatcherTest extends TestCase
{
    public function testCreate()
    {
        $testBackend = $this->createMock(MessageManagerBackend::class);

        $testBackend->expects($this->once())
            ->method('setDispatcher')
        ;

        $message = new Message();
        $message->setType('test');
        $message->setBody([]);

        $testBackend->expects($this->once())
            ->method('create')
            ->will($this->returnValue($message))
        ;

        $mMgr = $this->createMock(MessageManagerInterface::class);

        $mMgrBackend = new MessageManagerBackendDispatcher($mMgr, [], '', [['types' => ['test'], 'backend' => $testBackend]]);

        $this->assertSame($message, $mMgrBackend->create('test', []));
    }
}
