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

use Doctrine\Bundle\MongoDBBundle\DependencyInjection\Compiler\DoctrineMongoDBMappingsPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Salma Chakroun <chakroun.salma@gmail.com>
 */
final class MongoDBMappingCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $modelDir = realpath(__DIR__.'/../../Resources/config/doctrine');
        $mappings = array(
            $modelDir => 'Sonata\NotificationBundle\Document',
        );

        $container->addCompilerPass(
            DoctrineMongoDBMappingsPass::createXmlMappingDriver(
                $mappings, array('doctrine_mongodb.odm.default_document_manager')
            )
        );
    }
}
