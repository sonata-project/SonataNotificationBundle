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

use PHPUnit\Framework\TestCase;
use Sonata\NotificationBundle\Backend\BackendHealthCheck;
use Sonata\NotificationBundle\Backend\BackendInterface;
use ZendDiagnostics\Result\Success;

class BackendHealthCheckTest extends TestCase
{
    public function setUp(): void
    {
        if (!class_exists(Success::class)) {
            $this->markTestSkipped('ZendDiagnostics\Result\Success does not exist');
        }
    }

    public function testCheck(): void
    {
        $result = new Success('Test check', 'OK');

        $backend = $this->createMock(BackendInterface::class);
        $backend->expects($this->once())->method('getStatus')->will($this->returnValue($result));

        $health = new BackendHealthCheck($backend);

        $this->assertSame($result, $health->check());
    }
}
