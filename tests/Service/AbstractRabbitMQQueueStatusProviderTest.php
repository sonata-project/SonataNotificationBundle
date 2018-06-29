<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Service;

use PHPUnit\Framework\TestCase;

abstract class AbstractRabbitMQQueueStatusProviderTest extends TestCase
{
    protected $settings = [];

    protected function setUp()
    {
        $this->settings = [
            'console_url' => 'http://localhost:15672/api/',
            'user' => 'guest',
            'pass' => 'guest',
        ];
    }

    abstract public function testGetApiQueueStatus();
}
