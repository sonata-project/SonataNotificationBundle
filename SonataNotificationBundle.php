<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle;

use Sonata\CoreBundle\Form\FormHelper;
use Sonata\NotificationBundle\DependencyInjection\Compiler\NotificationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SonataNotificationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new NotificationCompilerPass());

        $this->registerFormMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        if (!defined('AMQP_DEBUG')) {
            //            define('AMQP_DEBUG', $this->container->getParameter('kernel.debug'));
            define('AMQP_DEBUG', false);
        }

        $this->registerFormMapping();
    }

    /**
     * Register form mapping information.
     */
    public function registerFormMapping()
    {
        FormHelper::registerFormTypeMapping(array(
            'sonata_notification_api_form_message' => 'Sonata\NotificationBundle\Form\Type\MessageSerializationType',
        ));
    }
}
