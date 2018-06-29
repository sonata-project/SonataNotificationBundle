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

use Sonata\NotificationBundle\Exception\MonitoringException;

// NEXT_MAJOR: remove this test
class RabbitMQQueueStatusGuzzleProviderTest extends AbstractRabbitMQQueueStatusProviderTest
{
    public function testGetApiQueueStatus()
    {
        $this->expectException(MonitoringException::class);
        $this->expectExceptionMessage('The guzzle http client library is required to run rabbitmq health checks. Make sure to add guzzlehttp/guzzle to your composer.json');
        $provider = new RabbitMQQueueStatusGuzzleProvider($this->settings);

        $provider->getApiQueueStatus();
    }
}
