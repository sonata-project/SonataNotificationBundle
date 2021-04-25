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
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Sonata\Doctrine\Bridge\Symfony\SonataDoctrineBundle;
use Sonata\Form\Bridge\Symfony\SonataFormBundle;
use Sonata\NotificationBundle\SonataNotificationBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle;
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

    public function registerBundles(): iterable
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
            new SwiftmailerBundle(),
            new SonataFormBundle(),
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

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        if (class_exists(Operation::class)) {
            $routes->import(__DIR__.'/Resources/config/routing/api_nelmio_v3.yml', '/', 'yaml');
        } else {
            $routes->import(__DIR__.'/Resources/config/routing/api_nelmio_v2.yml', '/', 'yaml');
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $loader->load(__DIR__.'/Resources/config/config.yml');
        $loader->load(__DIR__.'/Resources/config/security.yml');

        $container->setParameter('app.base_dir', $this->getBaseDir());
    }

    private function getBaseDir(): string
    {
        return sys_get_temp_dir().'/sonata-notification-bundle/var/';
    }
}
