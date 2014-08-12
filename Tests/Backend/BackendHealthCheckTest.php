<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Notification;

use Sonata\NotificationBundle\Backend\BackendHealthCheck;
use ZendDiagnostics\Result\Success;

class BackendHealthCheckTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('ZendDiagnostics\Result\Success')) {
            $this->markTestSkipped('ZendDiagnostics\Result\Success does not exist');
        }
    }

    public function testCheck()
    {
        $result = new Success('Test check', 'OK');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('getStatus')->will($this->returnValue($result));

        $health = new BackendHealthCheck($backend);

        $this->assertEquals($result, $health->check());
    }
}
