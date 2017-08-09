<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Backend;

use Sonata\NotificationBundle\Backend\MessageManagerBackendDispatcher;
use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Tests\Helpers\PHPUnit_Framework_TestCase;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class MessageManagerBackendDispatcherTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $testBackend = $this->createMock('Sonata\NotificationBundle\Backend\MessageManagerBackend');

        $testBackend->expects($this->once())
            ->method('setDispatcher')
        ;

        $message = new Message();
        $message->setType('test');
        $message->setBody(array());

        $testBackend->expects($this->once())
            ->method('create')
            ->will($this->returnValue($message))
        ;

        $mMgr = $this->createMock('Sonata\NotificationBundle\Model\MessageManagerInterface');

        $mMgrBackend = new MessageManagerBackendDispatcher($mMgr, array(), '', array(array('types' => array('test'), 'backend' => $testBackend)));

        $this->assertEquals($message, $mMgrBackend->create('test', array()));
    }
}
