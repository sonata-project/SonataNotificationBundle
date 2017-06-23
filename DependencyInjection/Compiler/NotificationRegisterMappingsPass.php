<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NotificationBundle\DependencyInjection\Compiler;

use Symfony\Bridge\Doctrine\DependencyInjection\CompilerPass\RegisterMappingsPass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Salma Khemiri <chakroun.salma@gmail.com>
 */
class NotificationRegisterMappingsPass extends RegisterMappingsPass
{
    public function __construct($driver, array $namespaces, array $managerParameters, $driverPattern, $enabledParameter = false)
    {
        parent::__construct($driver, $namespaces, $managerParameters, $driverPattern, $enabledParameter);
    }

    public static function createMongoDBMappingDriver($mappings)
    {
        $arguments = array($mappings, '.mongodb.xml');
        $locator = new Definition('Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator', $arguments);
        $driver = new Definition('Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver', array($locator));

        return new SonataNotificationRegisterMappingsPass($driver, $mappings, array('doctrine_mongodb.odm.default_document_manager'), 'doctrine_mongodb.odm.%s_metadata_driver');
    }
}
