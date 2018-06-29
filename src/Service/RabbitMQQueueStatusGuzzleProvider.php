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

use Guzzle\Http\Client as GuzzleClient;
use Sonata\NotificationBundle\Exception\MonitoringException;

/**
 * Class RabbitMQQueueStatusGuzzleProvider.
 *
 * @deprecated Create for BC. Use Sonata\NotificationBundle\Service\RabbitMQQueueStatusHttpProvider instead
 *
 * NEXT_MAJOR: remove this and use Sonata\NotificationBundle\Service\RabbitMQQueueStatusHttpProvider
 */
class RabbitMQQueueStatusGuzzleProvider implements RabbitMQQueueStatusProviderInterface
{
    /**
     * Array with RabbitMQ connection settings.
     *
     * @var array
     */
    protected $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function getApiQueueStatus()
    {
        if (!class_exists(GuzzleClient::class)) {
            throw new MonitoringException(
                'The guzzle http client library is required to run rabbitmq health checks. '
                .'Make sure to add guzzlehttp/guzzle to your composer.json.'
            );
        }

        $client = new GuzzleClient();
        $client->setConfig(['curl.options' => [CURLOPT_CONNECTTIMEOUT_MS => 3000]]);
        $request = $client->get(sprintf('%s/queues', $this->settings['console_url']));
        $request->setAuth($this->settings['user'], $this->settings['pass']);

        return json_decode($request->send()->getBody(true), true);
    }
}
