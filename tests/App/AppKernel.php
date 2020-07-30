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

namespace Sonata\NotificationBundle\Tests\App;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use FOS\RestBundle\FOSRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle;
use Sonata\NotificationBundle\SonataNotificationBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

final class AppKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', false);
    }

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SecurityBundle(),
            new TwigBundle(),
            new FOSRestBundle(),
            new SonataDoctrineBundle(),
            new SonataNotificationBundle(),
            new JMSSerializerBundle(),
            new DoctrineBundle(),
            new NelmioApiDocBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return $this->getBaseDir().'cache';
    }

    public function getLogDir(): string
    {
        return $this->getBaseDir().'log';
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import($this->getProjectDir().'/config/routes.yaml');
    }

    protected function configureContainer(ContainerBuilder $containerBuilder, LoaderInterface $loader): void
    {
        $containerBuilder->register('templating')->setSynthetic(true);
        $containerBuilder->register('templating.locator')->setSynthetic(true);
        $containerBuilder->register('templating.name_parser')->setSynthetic(true);
        $containerBuilder->register('mailer')->setSynthetic(true);

        $containerBuilder->loadFromExtension('framework', [
            'secret' => '50n474.U53r',
            'session' => [
                'handler_id' => 'session.handler.native_file',
                'storage_id' => 'session.storage.mock_file',
                'name' => 'MOCKSESSID',
            ],
            'translator' => null,
            'validation' => [
                'enabled' => true,
            ],
            'form' => [
                'enabled' => true,
            ],
            'assets' => null,
            'test' => true,
            'profiler' => [
                'enabled' => true,
                'collect' => false,
            ],
        ]);

        $containerBuilder->loadFromExtension('security', [
            'firewalls' => ['api' => ['anonymous' => true]],
            'providers' => ['in_memory' => ['memory' => null]],
        ]);

        $containerBuilder->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
            'exception_controller' => null,
        ]);

        $containerBuilder->loadFromExtension('doctrine', [
            'dbal' => [
                'connections' => [
                    'default' => [
                        'driver' => 'pdo_sqlite',
                    ],
                ],
            ],
            'orm' => [
                'default_entity_manager' => 'default',
            ],
        ]);

        $containerBuilder->loadFromExtension('fos_rest', [
            'param_fetcher_listener' => true,
        ]);
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/sonata-notification-bundle/var/';
    }
}
