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

use Liip\Monitor\Result\CheckResult;

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
        $result = new CheckResult('Test check', 'OK', CheckResult::OK);

        $backend = $this->getMock('Sonata\NotificationBundle\Backend\BackendInterface');
        $backend->expects($this->once())->method('getStatus')->will($this->returnValue($result));

        $health = new BackendHealthCheck($backend);

        $result = $health->check();

        $this->assertEquals(CheckResult::OK, $result->getStatus());
    }
}
