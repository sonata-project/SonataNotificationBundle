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

class RabbitMQQueueStatusHttpProvider implements RabbitMQQueueStatusProviderInterface
{
    /**
     * @var HttpClient
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * Array with RabbitMQ connection settings.
     *
     * @var array
     */
    protected $settings;

    public function __construct(array $settings, HttpClient $client, MessageFactory $messageFactory)
    {
        $this->settings = $settings;
        $this->messageFactory = $messageFactory;

        $this->client = new PluginClient(
            $client,
            [new AuthenticationPlugin(
                new BasicAuth($this->settings['user'], $this->settings['pass'])
            )]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getApiQueueStatus()
    {
        try {
            $request = $this->messageFactory->createRequest('GET', sprintf('%s/queues', $this->settings['console_url']));
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
