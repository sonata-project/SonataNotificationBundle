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

namespace Sonata\NotificationBundle;

use Sonata\NotificationBundle\DependencyInjection\Compiler\NotificationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataNotificationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new NotificationCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        if (!\defined('AMQP_DEBUG')) {
            //            define('AMQP_DEBUG', $this->container->getParameter('kernel.debug'));
            \define('AMQP_DEBUG', false);
        }
    }
}
