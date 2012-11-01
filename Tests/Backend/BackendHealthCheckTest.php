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

use Sonata\NotificationBundle\Backend\BackendHealthCheck;
use Sonata\NotificationBundle\Backend\BackendStatus;

class BackendHealthCheckTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!interface_exists('Liip\Monitor\Check\CheckInterface')) {
            $this->markTestSkipped('Liip\Monitor\Check\CheckInterface does not exist');
        }

    }
    public function testCheck()
    {
        $status = new BackendStatus(BackendStatus::OK, 'OK');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('getStatus')->will($this->returnValue($status));

        $health = new BackendHealthCheck($backend);

        $status = $health->check();

        $this->assertEquals(BackendStatus::OK, $status->getStatus());
    }
}
