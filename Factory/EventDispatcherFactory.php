<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Kernel;

final class EventDispatcherFactory
{
    public static function createEventDispatcher(ContainerInterface $container)
    {
        if (Kernel::MAJOR_VERSION <= 3 && Kernel::MINOR_VERSION < 3) {
            return new ContainerAwareEventDispatcher($container);
        }

        return new EventDispatcher();
    }
}
