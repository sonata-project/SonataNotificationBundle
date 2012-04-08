<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Tests\Page;

use Sonata\NotificationBundle\Backend\BackendHealthCheck;
use Sonata\NotificationBundle\Backend\BackendStatus;

class BackendHealthCheckTest extends \PHPUnit_Framework_TestCase
{
    public function testCheck()
    {
        $status = new BackendStatus(BackendStatus::SUCCESS, 'OK');

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('getStatus')->will($this->returnValue($status));

        $health = new BackendHealthCheck($backend);

        $status = $health->check();

        $this->assertEquals(BackendStatus::SUCCESS, $status->getStatus());
    }
}