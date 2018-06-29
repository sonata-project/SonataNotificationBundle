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

use Http\Message\MessageFactory;
use Http\Mock\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RabbitMQQueueStatusHttpProviderTest extends AbstractRabbitMQQueueStatusProviderTest
{
    /** @var Client */
    protected $client;

    /** @var MessageFactory */
    protected $messageFactory;

    public function setUp()
    {
        parent::setUp();

        $mockRequest = $this->createMock(RequestInterface::class);
        $mockRequest->expects($this->once())->method('withHeader')->will($this->returnValue($mockRequest));

        $this->messageFactory = $this->createMock(MessageFactory::class);
        $this->messageFactory->expects($this->once())->method('createRequest')->will($this->returnValue($mockRequest));

        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $mockResponse->expects($this->once())->method('getBody')->will($this->returnValue(json_encode([])));

        $this->client = $this->createMock(Client::class);
        $this->client->expects($this->once())->method('sendRequest')->will($this->returnValue($mockResponse));
    }

    public function testGetApiQueueStatus()
    {
        $provider = new RabbitMQQueueStatusHttpProvider($this->settings, $this->client, $this->messageFactory);

        $this->assertEquals([], $provider->getApiQueueStatus());
    }
}
