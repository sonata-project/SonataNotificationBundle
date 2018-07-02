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

use Http\Client\Common\Plugin\AuthenticationPlugin;
use Http\Client\Common\PluginClient;
use Http\Client\HttpClient;
use Http\Message\Authentication\BasicAuth;
use Http\Message\MessageFactory;
use Sonata\NotificationBundle\Exception\MonitoringException;

/**
 * @author Nikolay Mitrofanov <mitrofanovnk@gmail.com>
 */
final class RabbitMQQueueStatusHttpProvider implements RabbitMQQueueStatusProviderInterface
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var array
     */
    private $connectionSettings;

    public function __construct(array $settings, HttpClient $client, MessageFactory $messageFactory)
    {
        $this->connectionSettings = $settings;
        $this->messageFactory = $messageFactory;

        $this->client = new PluginClient(
            $client,
            [new AuthenticationPlugin(
                new BasicAuth($this->connectionSettings['user'], $this->connectionSettings['pass'])
            )]
        );
    }

    public function getApiQueueStatus()
    {
        try {
            $request = $this->messageFactory->createRequest('GET', sprintf('%s/queues', $this->connectionSettings['console_url']));
            $response = $this->client->sendRequest($request);
        } catch (\Exception $exception) {
            throw new MonitoringException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        } catch (\Http\Client\Exception $exception) {
            throw new MonitoringException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }
        try {
            if (200 === $response->getStatusCode()) {
                return json_decode($response->getBody(), true);
            }
        } catch (\RuntimeException $exception) {
            throw new MonitoringException($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
        }

        throw new MonitoringException($response->getStatusCode(), $response->getReasonPhrase());
    }
}
