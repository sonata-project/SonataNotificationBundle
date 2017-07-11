<?php

namespace Sonata\NotificationBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Kernel;

class EventDispatcherFactory
{
    public static function createEventDispatcher(ContainerInterface $serviceContainer)
    {
        if (Kernel::MAJOR_VERSION <= 3 && Kernel::MINOR_VERSION < 3) {
            return new ContainerAwareEventDispatcher($serviceContainer);
        }
        return new EventDispatcher();
    }
}
