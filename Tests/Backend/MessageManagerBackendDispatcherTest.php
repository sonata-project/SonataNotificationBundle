<?php
/*
 * This file is part of the Sonata package.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/


namespace Sonata\NotificationBundle\Tests\Backend;

use Sonata\NotificationBundle\Model\Message;
use Sonata\NotificationBundle\Backend\MessageManagerBackendDispatcher;

/**
 * @author Hugo Briand <briand@ekino.com>
 */
class MessageManagerBackendDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $testBackend = $this->getMockBuilder('Sonata\NotificationBundle\Backend\MessageManagerBackend')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $testBackend->expects($this->once())
            ->method('setDispatcher')
        ;

        $message = new Message();
        $message->setType("test");
        $message->setBody(array());

        $testBackend->expects($this->once())
            ->method('create')
            ->will($this->returnValue($message))
        ;

        $mMgr = $this->getMock('Sonata\NotificationBundle\Model\MessageManagerInterface');

        $mMgrBackend = new MessageManagerBackendDispatcher($mMgr, array(), '', array(array('types' => array('test'), 'backend' => $testBackend)));

        $this->assertEquals($message, $mMgrBackend->create("test", array()));
    }
}