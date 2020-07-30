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

namespace Sonata\NotificationBundle\Tests\Functional\Api;

use Sonata\NotificationBundle\Entity\BaseMessage;
use Sonata\NotificationBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class MessageControllerTest extends WebTestCase
{
    public function testPostMessageAction(): void
    {
        $kernel = new AppKernel();
        $kernel->boot();

        $message = new BaseMessage();
        $message->setBody(['Test']);

        $client = new KernelBrowser($kernel);
        $client->request('POST', '/api/notification/messages');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testGetMessagesAction(): void
    {
        $kernel = new AppKernel();
        $kernel->boot();

        $client = new KernelBrowser($kernel);
        $client->request('GET', '/api/notification/messages');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $client = new KernelBrowser($kernel);
        $client->request('GET', '/api/notification/messages.json');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/notification/messages.xml');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
