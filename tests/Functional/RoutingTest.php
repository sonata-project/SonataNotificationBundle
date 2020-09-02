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

namespace Sonata\NotificationBundle\Tests\Functional\Routing;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Sonata\NotificationBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Javier Spagnoletti <phansys@gmail.com>
 */
final class RoutingTest extends WebTestCase
{
    /**
     * @group legacy
     *
     * @dataProvider getRoutes
     */
    public function testRoutes(string $name, string $path, array $methods): void
    {
        $client = static::createClient();
        $router = $client->getContainer()->get('router');

        $route = $router->getRouteCollection()->get($name);

        $this->assertNotNull($route);
        $this->assertSame($path, $route->getPath());
        $this->assertEmpty(array_diff($methods, $route->getMethods()));
    }

    public function getRoutes(): iterable
    {
        // API
        if (class_exists(Operation::class)) {
            yield ['app.swagger_ui', '/api/doc', ['GET']];
            yield ['app.swagger', '/api/doc.json', ['GET']];
        } else {
            yield ['nelmio_api_doc_index', '/api/doc/{view}', ['GET']];
        }

        // API - Message
        yield ['sonata_api_notification_message_get_messages', '/api/notification/messages.{_format}', ['GET']];
        yield ['sonata_api_notification_message_post_message', '/api/notification/messages.{_format}', ['POST']];
    }

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }
}
