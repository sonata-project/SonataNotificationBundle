<?php

/*
 * This file is part of the Sonata project.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Sonata\NotificationBundle\DependencyInjection\Compiler\NotificationCompilerPass;

class SonataNotificationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NotificationCompilerPass());
    }

    public function boot()
    {
        if (!defined('AMQP_DEBUG')) {
//            define('AMQP_DEBUG', $this->container->getParameter('kernel.debug'));
            define('AMQP_DEBUG', false);
        }
    }
}
