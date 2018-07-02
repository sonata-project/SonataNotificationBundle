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

/**
 * @author Nikolay Mitrofanov <mitrofanovnk@gmail.com>
 */
interface RabbitMQQueueStatusProviderInterface
{
    /**
     * Calls the rabbitmq management api /api/<vhost>/queues endpoint to list the available queues.
     *
     * @see https://cdn.rawgit.com/rabbitmq/rabbitmq-management/master/priv/www/api/index.html
     *
     * @throws MonitoringException
     *
     * @return array|null
     */
    public function getApiQueueStatus();
}
